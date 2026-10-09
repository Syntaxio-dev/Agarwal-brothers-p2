<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * A link an admin may place in a button: a path on this site ("/verticals")
 * or an absolute http(s) URL. Rejects javascript:, data:, protocol-relative
 * "//host" and backslash tricks that browsers treat as another host.
 */
class SafeLink implements ValidationRule
{
    public static function passes(?string $value): bool
    {
        $value = (string) $value;

        if ($value === '' || preg_match('/[\s\x00-\x1F\x7F]/', $value)) {
            return false;
        }

        if (str_starts_with($value, '/')) {
            return ! str_starts_with($value, '//') && ! str_starts_with($value, '/\\');
        }

        return (bool) preg_match('~^https?://[^\s/\\\\?#]+~i', $value)
            && filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! self::passes(is_string($value) ? $value : null)) {
            $fail('Use a site path like /verticals or a full https:// link.');
        }
    }
}
