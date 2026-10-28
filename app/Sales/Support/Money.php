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

    /**
     * Whole paise -> the short Indian form the management page uses: "₹62K", "₹4.82L", "₹38.6L", "₹1.2Cr".
     * Three significant digits, trailing zeros dropped; under a thousand rupees it is the plain number ("₹850").
     */
    public static function short(int $paise): string
    {
        $sign = $paise < 0 ? '-' : '';
        $rupees = abs($paise) / 100;

        // three significant digits for a number scaled into 1 .. 999, no trailing zeros ("4.80" -> "4.8", "62.0" -> "62")
        $trim = function (float $value): string {
            $digits = $value >= 100 ? 0 : ($value >= 10 ? 1 : 2);
            $text = number_format($value, $digits, '.', '');

            // only the zeros after the decimal point go ("4.80" -> "4.8"); the zeros of "120" stay
            return str_contains($text, '.') ? rtrim(rtrim($text, '0'), '.') : $text;
        };

        if (round($rupees) < 1000) {
            return $sign . '₹' . number_format((int) round($rupees), 0, '.', '');
        }

        // a number that rounds up to the next unit ("99.96K") moves up: it reads "1L", never "100K"
        $k = (float) $trim($rupees / 1000);
        if ($k < 100) {
            return $sign . '₹' . $trim($rupees / 1000) . 'K';
        }

        $l = (float) $trim($rupees / 100_000);
        if ($l < 100) {
            return $sign . '₹' . $trim($rupees / 100_000) . 'L';
        }

        return $sign . '₹' . $trim($rupees / 10_000_000) . 'Cr';
    }
}
