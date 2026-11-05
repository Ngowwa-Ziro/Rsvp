<?php

namespace App\Support;

class Phone
{
    /** Returns +254XXXXXXXXX for valid Kenyan mobiles (07xx, 01xx, 254..., +254...), else null. */
    public static function normalize(?string $raw): ?string
    {
        if ($raw === null) {
            return null;
        }
        $digits = preg_replace('/[\s\-().]/', '', $raw);
        if (preg_match('/^(?:\+?254|0)([17]\d{8})$/', $digits, $m)) {
            return '+254'.$m[1];
        }
        return null;
    }

    public static function whatsapp(string $e164): string
    {
        return ltrim($e164, '+');
    }
}
