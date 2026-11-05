<?php

namespace App\Services;

use App\Models\Invitation;

class InvitationCodeGenerator
{
    // No 0/O/1/I/L so codes survive being read out over a phone call.
    private const ALPHABET = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    public function generate(int $length = 6): string
    {
        do {
            $code = '';
            for ($i = 0; $i < $length; $i++) {
                $code .= self::ALPHABET[random_int(0, strlen(self::ALPHABET) - 1)];
            }
        } while (Invitation::where('code', $code)->exists());

        return $code;
    }
}
