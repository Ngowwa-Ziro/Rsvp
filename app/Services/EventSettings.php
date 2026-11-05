<?php

namespace App\Services;

use App\Enums\GuestCategory;
use App\Models\Setting;
use Carbon\Carbon;

class EventSettings
{
    public const TZ = 'Africa/Nairobi';

    public const DEFAULTS = [
        'graduate_name' => 'Joseph Ziro Ngowwa',
        'event_title' => 'Graduation Celebration',
        'starts_at' => '2026-12-05 12:00',
        'venue' => 'Jakingardens, Ongata Rongai, Kenya',
        'maps_url' => '',
        'invitation_message' => 'It would mean the world to celebrate this milestone with the people who helped me reach it. Please join me for an afternoon of good food, good company and gratitude.',
        'rsvp_deadline' => '2026-11-25',
        'capacity' => '',
        'public_url' => '',
        'family_max' => '6',
        'friend_max' => '2',
    ];

    public function get(string $key): string
    {
        return (string) Setting::get($key, self::DEFAULTS[$key] ?? '');
    }

    public function all(): array
    {
        return collect(self::DEFAULTS)->keys()->mapWithKeys(fn ($k) => [$k => $this->get($k)])->all();
    }

    public function startsAt(): Carbon
    {
        return Carbon::parse($this->get('starts_at'), self::TZ);
    }

    /** RSVPs are accepted through the end of the deadline day, Nairobi time. */
    public function deadline(): Carbon
    {
        return Carbon::parse($this->get('rsvp_deadline'), self::TZ)->endOfDay();
    }

    public function capacity(): ?int
    {
        $c = $this->get('capacity');

        return $c === '' ? null : (int) $c;
    }

    public function maxFor(GuestCategory $category): int
    {
        return (int) $this->get($category->value.'_max') ?: $category->defaultMaxAttendees();
    }
}
