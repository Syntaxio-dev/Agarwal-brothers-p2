<?php

namespace App\Sales\Support;

/**
 * One spelling for a phone number, so "98765 43210", "09876543210" and "+91 98765-43210" are the same client.
 * The same rules as the app (src/core/phone.js). Indian mobile numbers get +91; other numbers need their own country code.
 */
class PhoneNumber
{
    /** @return ?string E.164 such as +919876543210, or null when the text is not a usable number */
    public static function normalize(?string $raw): ?string
    {
        if ($raw === null) {
            return null;
        }

        $raw = trim($raw);
        $plus = str_starts_with($raw, '+') || str_starts_with($raw, '00');
        $digits = preg_replace('/\D+/', '', $raw) ?? '';

        if (str_starts_with($raw, '00')) {
            $digits = substr($digits, 2);
        }

        if ($digits === '') {
            return null;
        }

        if ($plus) {
            return strlen($digits) >= 8 && strlen($digits) <= 15 ? '+' . $digits : null;
        }

        return match (true) {
            strlen($digits) === 10 && $digits[0] >= '6' => '+91' . $digits,
            strlen($digits) === 11 && $digits[0] === '0' => '+91' . substr($digits, 1),
            strlen($digits) === 12 && str_starts_with($digits, '91') => '+' . $digits,
            default => null,
        };
    }
}
