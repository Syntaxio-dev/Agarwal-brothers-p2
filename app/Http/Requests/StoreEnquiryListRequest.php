<?php

namespace App\Http\Requests;

use App\Support\FormRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreEnquiryListRequest extends FormRequest
{
    public const MAX_ITEMS = 20;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => FormRules::name(),
            'email' => ['required', 'email', 'max:255'],
            'phone' => FormRules::phone(false),
            'phone_country' => FormRules::phoneCountry(),
            'company' => ['nullable', 'string', 'max:255'],
            'order_location' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string', 'max:3000'],
            'items' => ['required', 'array', 'min:1', 'max:' . self::MAX_ITEMS],
            'items.*.slug' => ['required', 'string', 'max:190', 'regex:/^[A-Za-z0-9-]+$/'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:999'],
            // Honeypot: real users never fill this in.
            'website' => ['prohibited'],
        ];
    }

    protected function prepareForValidation(): void
    {
        FormRules::prepare($this);
    }

    protected function getRedirectUrl(): string
    {
        return parent::getRedirectUrl() . '#enquiry-details';
    }

    public function messages(): array
    {
        return FormRules::messages() + [
            'items.required' => 'Your enquiry list is empty. Add at least one product.',
            'items.min' => 'Your enquiry list is empty. Add at least one product.',
            'items.max' => 'You can send up to ' . self::MAX_ITEMS . ' products in one enquiry.',
        ];
    }
}
