<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Shared validation for the public forms: names are letters only, phones are exactly
 * ten digits plus a chosen country. Keep the browser rules in resources/js/app.js (formGuard) in sync.
 */
class FormRules
{
    public static function name(bool $required = true): array
    {
        return [$required ? 'required' : 'nullable', 'string', 'min:2', 'max:80', "regex:/^\\p{L}[\\p{L}\\p{M} .'’-]*$/u"];
    }

    public static function phone(bool $required = true): array
    {
        return [$required ? 'required' : 'nullable', 'regex:/^\\d{10}$/'];
    }

    public static function phoneCountry(): array
    {
        return ['nullable', Rule::in(DialCodes::isos())];
    }

    public static function messages(): array
    {
        return [
            'name.regex' => "Name can only contain letters, spaces and . ' -",
            'name.min' => 'Please enter your full name.',
            'phone.regex' => 'Enter a 10-digit mobile number (digits only, without the country code).',
        ];
    }

    /** Tidy the input before validating: collapse name spaces, drop spaces and dashes from the number. */
    public static function prepare(Request $request): void
    {
        $merge = [];

        if (is_string($request->input('name'))) {
            $merge['name'] = trim(preg_replace('/\s+/u', ' ', $request->input('name')));
        }
        if (is_string($request->input('phone'))) {
            $merge['phone'] = preg_replace('/[\s-]+/', '', $request->input('phone'));
        }

        $request->merge($merge);
    }

    /** After validation: turn the 10 digits + country into "+91 9876543210" and drop the helper field. */
    public static function finish(array $data): array
    {
        if (filled($data['phone'] ?? null)) {
            $data['phone'] = '+' . DialCodes::code($data['phone_country'] ?? null) . ' ' . $data['phone'];
        }
        unset($data['phone_country']);

        return $data;
    }
}
