<?php

namespace App\Http\Controllers\Admin;

use App\Enums\GuestCategory;
use App\Http\Controllers\Controller;
use App\Http\Requests\InvitationRequest;
use App\Http\Requests\RsvpSubmitRequest;
use App\Models\Invitation;
use App\Services\EventSettings;
use App\Services\InvitationCodeGenerator;
use App\Services\RsvpService;
use Illuminate\Http\Request;

class InvitationController extends Controller
{
    public function index(Request $request)
    {
        $q = Invitation::query()->with('rsvp');

        if ($s = trim((string) $request->query('q'))) {
            $q->where(fn ($w) => $w->where('name', 'like', "%$s%")->orWhere('phone', 'like', "%$s%")->orWhere('code', 'like', "%$s%"));
        }
        if ($cat = $request->query('category')) {
            $q->where('category', $cat);
        }
        match ($request->query('status')) {
            'revoked' => $q->whereNotNull('revoked_at'),
            'pending', 'attending', 'declined' => $q->active()->where('status', $request->query('status')),
            default => null,
        };

        return view('admin.invitations.index', [
            'invitations' => $q->orderBy('name')->paginate(25)->withQueryString(),
            'event' => app(EventSettings::class),
        ]);
    }

    public function create(EventSettings $event)
    {
        return view('admin.invitations.form', ['invitation' => new Invitation(['category' => GuestCategory::Friend]), 'event' => $event]);
    }

    public function store(InvitationRequest $request, InvitationCodeGenerator $codes)
    {
        $data = $request->validated();
        $data['code'] = $codes->generate();
        $invitation = Invitation::create($data);

        return redirect()->route('admin.invitations.show', $invitation)->with('status', "Invitation created. Code: {$invitation->code}");
    }

    public function show(Invitation $invitation)
    {
        return view('admin.invitations.show', ['invitation' => $invitation->load('rsvp.guests')]);
    }

    public function edit(Invitation $invitation, EventSettings $event)
    {
        return view('admin.invitations.form', compact('invitation', 'event'));
    }

    public function update(InvitationRequest $request, Invitation $invitation)
    {
        $invitation->update($request->validated());

        return redirect()->route('admin.invitations.show', $invitation)->with('status', 'Invitation updated.');
    }

    /** "Delete" revokes, so the history stays intact and the code stops working. */
    public function destroy(Invitation $invitation)
    {
        $invitation->update(['revoked_at' => now()]);

        return redirect()->route('admin.invitations.index')->with('status', 'Invitation revoked.');
    }

    public function restore(Invitation $invitation)
    {
        $invitation->update(['revoked_at' => null]);

        return back()->with('status', 'Invitation restored.');
    }

    /** Change an RSVP on behalf of a guest (ignores the deadline). */
    public function respond(RsvpSubmitRequest $request, Invitation $invitation, RsvpService $rsvp)
    {
        $rsvp->submit($invitation, $request->validated(), bypassDeadline: true);

        return redirect()->route('admin.invitations.show', $invitation)->with('status', 'RSVP updated.');
    }
}
