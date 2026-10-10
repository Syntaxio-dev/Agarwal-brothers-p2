<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Support\FormRules;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class StoreEnquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => [
                'required',
                'integer',
                function (string $attribute, mixed $value, Closure $fail) {
                    $listed = Product::whereKey($value)
                        ->where('is_active', true)
                        ->whereHas('category.brand', fn ($q) => $q->where('is_active', true))
                        ->exists();

                    if (! $listed) {
                        $fail('This product is no longer available for enquiry.');
                    }
                },
            ],
            'name' => FormRules::name(),
            'email' => ['required', 'email', 'max:255'],
            'phone' => FormRules::phone(false),
            'phone_country' => FormRules::phoneCountry(),
            'budget' => ['nullable', 'string', 'max:255'],
            'order_location' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string', 'max:3000'],
            // Honeypot: real users never fill this in.
            'website' => ['prohibited'],
        ];
    }

    protected function prepareForValidation(): void
    {
        FormRules::prepare($this);
    }

    public function messages(): array
    {
        return FormRules::messages();
    }

    /** Send the visitor back to the form (not the top of the page) when something is wrong. */
    protected function getRedirectUrl(): string
    {
        return parent::getRedirectUrl() . '#enquiry-form';
    }

    /** Only these fields are ever stored (the honeypot is never persisted). */
    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        return is_array($data) ? FormRules::finish(array_diff_key($data, ['website' => true])) : $data;
    }
}
