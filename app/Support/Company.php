<?php

namespace App\Support;

use App\Models\SiteSetting;

/** The facts the site quotes about the company (see config/company.php). */
class Company
{
    /** Year the company started: Site Settings first, then config/company.php. */
    public static function foundedYear(): int
    {
        return (int) (SiteSetting::current()?->founded_year ?: config('company.founded'));
    }

    /** Whole years since the start (never below 1). */
    public static function years(): int
    {
        return max(1, now()->year - self::foundedYear());
    }

    /** The number shown to visitors: whole years rounded down to a multiple of 5 (33 -> 30, 35 -> 35), always true as "N+". */
    public static function yearsShown(): int
    {
        return max(5, intdiv(self::years(), 5) * 5);
    }

    /** "1000+" style text for customers, industries or brands. */
    public static function plus(string $key): string
    {
        return config("company.{$key}") . '+';
    }
}
