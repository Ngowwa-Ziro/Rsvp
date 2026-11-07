<?php

namespace App\Services;

use App\Models\Invitation;
use App\Support\Phone;

class InviteMessageBuilder
{
    public function __construct(private EventSettings $event) {}

    /** First send is the invitation itself; every later send is a reminder. */
    public function isReminder(Invitation $i): bool
    {
        return $i->send_count >= 1;
    }

    public function text(Invitation $i): string
    {
        $when = $this->event->startsAt()->format('l, j F Y');
        $deadline = $this->event->deadline()->format('j F');
        $link = route('rsvp.landing', ['code' => $i->code]);
        $who = $this->event->get('graduate_name');
        $title = $this->event->get('event_title');

        if ($this->isReminder($i)) {
            return "Reminder: Hi {$i->name}, we haven't received your RSVP for {$who}'s {$title} on {$when} yet. "
                ."Please reply by {$deadline} using your code {$i->code}: {$link}";
        }

        return "Hi {$i->name}, you're invited to {$who}'s {$title} on {$when} at ".$this->event->get('venue').". "
            ."Please RSVP by {$deadline} using your invitation code {$i->code}: {$link}";
    }

    public function whatsappUrl(Invitation $i): string
    {
        return 'https://wa.me/'.Phone::whatsapp($i->phone).'?text='.rawurlencode($this->text($i));
    }
}
