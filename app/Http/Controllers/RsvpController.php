<?php

namespace App\Http\Controllers;

use App\Http\Requests\RsvpSubmitRequest;
use App\Models\Invitation;
use App\Services\EventSettings;
use App\Services\RsvpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class RsvpController extends Controller
{
    public function __construct(private RsvpService $rsvp, private EventSettings $event) {}

    public function landing(Request $request)
    {
        return view('public.landing', [
            'e' => $this->event->all(),
            'startsAt' => $this->event->startsAt(),
            'deadline' => $this->event->deadline(),
            'open' => $this->rsvp->isOpen(),
            'prefill' => strtoupper((string) $request->query('code', '')),
        ]);
    }

    public function lookup(Request $request)
    {
        $request->validate(['code' => ['required', 'string', 'max:20']], [
            'code.required' => 'Please enter your invitation code.',
        ]);

        if (! $this->rsvp->isOpen()) {
            return back()->withInput()->withErrors([
                'code' => 'RSVPs closed on '.$this->event->deadline()->format('j F Y').'. Please contact Joseph directly.',
            ]);
        }

        $invitation = $this->rsvp->findByCode($request->string('code'));
        if (! $invitation) {
            return back()->withInput()->withErrors([
                'code' => 'We could not find that invitation code. Check it and try again.',
            ]);
        }

        $request->session()->regenerate();
        $request->session()->put('rsvp.invitation_id', $invitation->id);

        return redirect()->route('rsvp.form');
    }

    public function form(Request $request)
    {
        $invitation = $this->sessionInvitation($request);
        if (! $invitation) {
            return redirect()->route('rsvp.landing');
        }

        return view('public.respond', [
            'e' => $this->event->all(),
            'startsAt' => $this->event->startsAt(),
            'invitation' => $invitation,
            'rsvp' => $invitation->rsvp?->load('guests'),
        ]);
    }

    public function submit(RsvpSubmitRequest $request)
    {
        $invitation = $this->sessionInvitation($request);
        if (! $invitation) {
            return redirect()->route('rsvp.landing');
        }

        $rsvp = $this->rsvp->submit($invitation, $request->validated());

        if ($request->filled('email')) {
            $invitation->update(['email' => $request->string('email')]);
        }
        $this->sendEmail($invitation->fresh(), $rsvp);

        $request->session()->put('rsvp.done', $invitation->id);

        return redirect()->route('rsvp.confirmation');
    }

    public function confirmation(Request $request)
    {
        $invitation = $this->sessionInvitation($request);
        if (! $invitation || $request->session()->get('rsvp.done') !== $invitation->id) {
            return redirect()->route('rsvp.landing');
        }

        return view('public.confirmation', [
            'e' => $this->event->all(),
            'startsAt' => $this->event->startsAt(),
            'invitation' => $invitation,
            'rsvp' => $invitation->rsvp->load('guests'),
            'emailEnabled' => $this->emailConfigured(),
        ]);
    }

    public function calendar()
    {
        $start = $this->event->startsAt()->utc();
        $end = $start->copy()->addHours(6);
        $esc = fn (string $s) => addcslashes($s, ",;\\");
        $lines = [
            'BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//Graduation RSVP//EN', 'BEGIN:VEVENT',
            'UID:'.md5($start->toIso8601String()).'@graduation-rsvp',
            'DTSTAMP:'.now()->utc()->format('Ymd\THis\Z'),
            'DTSTART:'.$start->format('Ymd\THis\Z'),
            'DTEND:'.$end->format('Ymd\THis\Z'),
            'SUMMARY:'.$esc($this->event->get('event_title').' - '.$this->event->get('graduate_name')),
            'LOCATION:'.$esc($this->event->get('venue')),
            'DESCRIPTION:'.$esc('Directions: '.$this->event->get('maps_url')),
            'END:VEVENT', 'END:VCALENDAR',
        ];

        return response(implode("\r\n", $lines)."\r\n", 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="graduation-celebration.ics"',
        ]);
    }

    private function sessionInvitation(Request $request): ?Invitation
    {
        $id = $request->session()->get('rsvp.invitation_id');

        return $id ? Invitation::active()->find($id) : null;
    }

    private function emailConfigured(): bool
    {
        return ! in_array(config('mail.default'), ['log', 'array', null], true);
    }

    private function sendEmail(Invitation $invitation, $rsvp): void
    {
        if (! $invitation->email || ! $this->emailConfigured()) {
            return;
        }
        $body = $rsvp->attending
            ? "Hello {$invitation->name},\n\nYour RSVP is confirmed for {$rsvp->guest_count} guest(s).\n\n"
            : "Hello {$invitation->name},\n\nWe have recorded that you will not be able to attend. Thank you for letting us know.\n\n";
        $body .= $this->event->get('event_title').': '.$this->event->startsAt()->format('l, j F Y \a\t g:i A')."\n"
            .'Venue: '.$this->event->get('venue')."\n"
            .'Event page: '.route('rsvp.landing')."\n";

        try {
            Mail::raw($body, fn ($m) => $m->to($invitation->email)->subject('Your RSVP - '.$this->event->get('event_title')));
        } catch (\Throwable $e) {
            Log::warning('RSVP confirmation email failed: '.$e->getMessage());
        }
    }
}
