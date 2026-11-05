<?php

namespace App\Enums;

enum GuestCategory: string
{
    case Family = 'family';
    case Friend = 'friend';

    /** Fallback total allowance (invitee + additional). Admin can change it in Settings. */
    public function defaultMaxAttendees(): int
    {
        return match ($this) {
            self::Family => 6, // invitee + 5
            self::Friend => 2, // invitee + 1
        };
    }

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
