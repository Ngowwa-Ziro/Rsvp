<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\Rsvp;
use App\Models\RsvpGuest;

class StatsService
{
    public function __construct(private EventSettings $event) {}

    public function summary(): array
    {
        $issued = Invitation::active()->count();
        $confirmed = Invitation::active()->where('status', 'attending')->count();
        $declined = Invitation::active()->where('status', 'declined')->count();
        $pending = Invitation::active()->where('status', 'pending')->count();

        // Expected attendance = people, not forms.
        $expected = (int) Rsvp::where('attending', true)
            ->whereHas('invitation', fn ($q) => $q->whereNull('revoked_at'))
            ->sum('guest_count');

        $allocated = (int) Invitation::active()->sum('max_attendees');
        $capacity = $this->event->capacity();

        $partiesWithExtras = Rsvp::where('attending', true)->where('guest_count', '>', 1)
            ->whereHas('invitation', fn ($q) => $q->whereNull('revoked_at'))->count();

        $byCategory = Invitation::active()
            ->join('rsvps', 'rsvps.invitation_id', '=', 'invitations.id')
            ->where('rsvps.attending', true)
            ->selectRaw('invitations.category as category, COUNT(*) as parties, SUM(rsvps.guest_count) as people')
            ->groupBy('invitations.category')->get()->keyBy('category');

        return [
            'issued' => $issued,
            'confirmed' => $confirmed,
            'declined' => $declined,
            'pending' => $pending,
            'expected' => $expected,
            'additional_guests' => max($expected - $confirmed, 0),
            'parties_with_extras' => $partiesWithExtras,
            'allocated' => $allocated,
            'remaining_allocated' => max($allocated - $expected, 0),
            'capacity' => $capacity,
            'capacity_left' => $capacity === null ? null : $capacity - $expected,
            'response_rate' => $issued ? round((($confirmed + $declined) / $issued) * 100) : 0,
            'checked_in' => RsvpGuest::whereNotNull('checked_in_at')->count(),
            'family_people' => (int) ($byCategory['family']->people ?? 0),
            'friend_people' => (int) ($byCategory['friend']->people ?? 0),
        ];
    }
}
