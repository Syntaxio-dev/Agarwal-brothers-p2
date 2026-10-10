<?php

namespace App\Support;

use App\Models\ContactMessage;
use App\Models\Enquiry;
use App\Models\JobApplication;
use Illuminate\Database\Eloquent\Model;

/** Fills {placeholders} in the editable e-mail texts with the details of one enquiry, message or application. */
class EmailTemplates
{
    /** @return array<string, string> placeholder => what it becomes (shown to the person editing a template) */
    public static function placeholders(): array
    {
        return [
            '{name}' => 'Full name of the customer',
            '{first_name}' => 'First name only',
            '{reference}' => 'Reference number, e.g. ENQ-000123',
            '{products}' => 'List of the products asked about (enquiries)',
            '{position}' => 'Job title (applications)',
            '{position_line}' => 'Short phrase such as " for the position of Sales Executive" (applications)',
            '{company_name}' => 'Agarwal Brothers',
            '{phone}' => 'Main phone number of the company',
            '{email}' => 'Main e-mail address of the company',
            '{hours}' => 'Working hours',
        ];
    }

    public static function render(string $text, Model $record): string
    {
        $name = trim((string) ($record->name ?? ''));

        $values = [
            '{name}' => $name,
            '{first_name}' => explode(' ', $name)[0] ?? $name,
            '{reference}' => static::reference($record),
            '{products}' => static::products($record),
            '{position}' => (string) ($record->position ?? ''),
            '{position_line}' => filled($record->position ?? null) ? ' for the position of ' . $record->position : '',
            '{company_name}' => 'Agarwal Brothers',
            '{phone}' => (string) config('contact.call'),
            '{email}' => (string) config('contact.mail_24x7'),
            '{hours}' => (string) (config('contact.working_days.0') ?? ''),
        ];

        return strtr($text, $values);
    }

    public static function reference(Model $record): string
    {
        $prefix = match (true) {
            $record instanceof Enquiry => 'ENQ',
            $record instanceof JobApplication => 'APP',
            $record instanceof ContactMessage => 'MSG',
            default => 'REF',
        };

        return $prefix . '-' . str_pad((string) $record->getKey(), 6, '0', STR_PAD_LEFT);
    }

    /** "- Entris II (qty 2)" lines, or the single product, or a neutral phrase. */
    private static function products(Model $record): string
    {
        if ($record instanceof Enquiry) {
            $record->loadMissing('items', 'product');

            if ($record->items->isNotEmpty()) {
                return $record->items->map(fn ($i) => '- ' . $i->product_name . ($i->quantity > 1 ? ' (qty ' . $i->quantity . ')' : ''))->implode("\n");
            }
            if ($record->product) {
                return '- ' . $record->product->name;
            }
        }

        if ($record instanceof ContactMessage && filled($record->subject)) {
            return '- ' . $record->subject;
        }

        return '- your requirement';
    }
}
