<?php

namespace App\Sales\Support;

/** Whole paise -> "₹1,23,456.50" with Indian digit grouping (the same look as the app). Integer maths only. */
class Money
{
    public static function rupees(int $paise, bool $withPaise = true): string
    {
        $sign = $paise < 0 ? '-' : '';
        $abs = abs($paise);
        $whole = (string) intdiv($abs, 100);

        $grouped = strlen($whole) <= 3
            ? $whole
            : preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', substr($whole, 0, -3)) . ',' . substr($whole, -3);

        $fraction = $withPaise || $abs % 100 !== 0 ? '.' . str_pad((string) ($abs % 100), 2, '0', STR_PAD_LEFT) : '';

        return $sign . '₹' . $grouped . $fraction;
    }
}
