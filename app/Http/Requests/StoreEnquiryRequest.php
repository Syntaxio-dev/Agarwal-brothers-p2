<?php

namespace App\Http\Requests;

use App\Models\Product;
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'budget' => ['nullable', 'string', 'max:255'],
            'order_location' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string', 'max:3000'],
            // Honeypot: real users never fill this in.
            'website' => ['prohibited'],
        ];
    }

    /** Only these fields are ever stored (the honeypot is never persisted). */
    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        return is_array($data) ? array_diff_key($data, ['website' => true]) : $data;
    }
}
