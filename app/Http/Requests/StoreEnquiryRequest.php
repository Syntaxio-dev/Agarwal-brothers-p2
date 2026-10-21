<?php

namespace App\Http\Requests;

use App\Models\EnquiryQuestion;
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
            'answers' => ['nullable', 'array'],
            'answers.*' => ['nullable', 'string', 'max:300'],
            // Honeypot: real users never fill this in.
            'website' => ['prohibited'],
        ];
    }

    /** Every question the form shows for this product must be answered (and a pick-one answer must be a listed choice). */
    public function after(): array
    {
        return [function (\Illuminate\Validation\Validator $validator) {
            $product = Product::with('category')->find($this->input('product_id'));
            if (! $product) {
                return;
            }

            $given = (array) $this->input('answers', []);

            foreach (EnquiryQuestion::forProduct($product) as $q) {
                $value = trim((string) ($given[$q->id] ?? ''));

                if ($value === '' || ($q->type === 'choice' && ! in_array($value, $q->optionList(), true))) {
                    $validator->errors()->add('answers.' . $q->id, $q->type === 'choice' ? 'Please choose an answer.' : 'Please answer this question.');
                }
            }
        }];
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

        if (! is_array($data)) {
            return $data;
        }

        $data = FormRules::finish(array_diff_key($data, ['website' => true]));

        // answers arrive keyed by question id; keep only questions this product really shows and, for
        // pick-one questions, only a listed choice. Stored as a snapshot of the question text + answer.
        $given = (array) ($data['answers'] ?? []);
        unset($data['answers']);

        $product = Product::with('category')->find($data['product_id'] ?? 0);
        $answers = $product
            ? EnquiryQuestion::forProduct($product)->map(function (EnquiryQuestion $q) use ($given) {
                $value = trim((string) ($given[$q->id] ?? ''));
                if ($value === '' || ($q->type === 'choice' && ! in_array($value, $q->optionList(), true))) {
                    return null;
                }

                return ['question' => $q->question, 'answer' => $value];
            })->filter()->values()->all()
            : [];

        if ($answers) {
            $data['answers'] = $answers;
        }

        return $data;
    }
}
