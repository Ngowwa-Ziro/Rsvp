<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\Rsvp;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RsvpService
{
    public function __construct(private EventSettings $event) {}

    public function isOpen(): bool
    {
        return now()->lte($this->event->deadline());
    }

    public function findByCode(string $raw): ?Invitation
    {
        $code = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $raw));

        return $code === '' ? null : Invitation::active()->where('code', $code)->first();
    }

    /**
     * Create or update the single RSVP for an invitation.
     * $data: attending (bool), guests (string[]), dietary, message.
     */
    public function submit(Invitation $invitation, array $data, bool $bypassDeadline = false): Rsvp
    {
        if (! $bypassDeadline && ! $this->isOpen()) {
            throw ValidationException::withMessages([
                'attending' => 'RSVPs closed on '.$this->event->deadline()->format('j F Y').'. Please contact Joseph directly.',
            ]);
        }

        return DB::transaction(function () use ($invitation, $data) {
            $inv = Invitation::whereKey($invitation->id)->lockForUpdate()->firstOrFail();

            if ($inv->isRevoked()) {
                throw ValidationException::withMessages(['attending' => 'This invitation is no longer active.']);
            }

            $attending = filter_var($data['attending'], FILTER_VALIDATE_BOOLEAN);
            $names = $attending
                ? array_values(array_filter(array_map(fn ($n) => trim((string) $n), $data['guests'] ?? [])))
                : [];

            if ($attending) {
                if (count($names) < 1) {
                    throw ValidationException::withMessages(['guests' => 'Please enter at least one attendee name.']);
                }
                if (count($names) > $inv->max_attendees) {
                    throw ValidationException::withMessages([
                        'guests' => "This invitation allows up to {$inv->max_attendees} people including you.",
                    ]);
                }
                $this->assertCapacity($inv, count($names));
            }

            $rsvp = Rsvp::updateOrCreate(
                ['invitation_id' => $inv->id],
                [
                    'attending' => $attending,
                    'guest_count' => count($names),
                    'dietary' => $attending ? ($data['dietary'] ?? null) : null,
                    'message' => $data['message'] ?? null,
                ]
            );

            // Re-sync guest rows, keeping check-in state for guests whose name is unchanged.
            $checkedIn = $rsvp->guests()->get()->mapWithKeys(
                fn ($g) => [mb_strtolower($g->name) => $g->checked_in_at]
            );
            $rsvp->guests()->delete();
            foreach ($names as $name) {
                $rsvp->guests()->create([
                    'name' => $name,
                    'checked_in_at' => $checkedIn[mb_strtolower($name)] ?? null,
                ]);
            }

            $inv->update([
                'status' => $attending ? 'attending' : 'declined',
                'responded_at' => now(),
            ]);

            return $rsvp->load('guests');
        });
    }

    private function assertCapacity(Invitation $inv, int $requested): void
    {
        $capacity = $this->event->capacity();
        if ($capacity === null) {
            return;
        }
        $others = (int) Rsvp::where('attending', true)
            ->where('invitation_id', '!=', $inv->id)
            ->whereHas('invitation', fn ($q) => $q->whereNull('revoked_at'))
            ->sum('guest_count');

        if ($others + $requested > $capacity) {
            throw ValidationException::withMessages([
                'guests' => 'Sorry, the venue is at capacity. Please contact Joseph to arrange your attendance.',
            ]);
        }
    }
}
