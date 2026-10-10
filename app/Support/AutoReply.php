<?php

namespace App\Support;

use App\Mail\CustomerMessage;
use App\Models\EmailTemplate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

/**
 * The automatic "we have received your enquiry / message / application" e-mail.
 * What it says, and whether it is sent at all, is edited under Settings, Email templates.
 */
class AutoReply
{
    /** Stops the form from being used to flood one address with mail. */
    public const PER_ADDRESS_PER_DAY = 3;

    public static function send(string $templateKey, Model $record): bool
    {
        try {
            $template = EmailTemplate::forKey($templateKey);
            $email = strtolower(trim((string) ($record->email ?? '')));

            if (! $template || ! $template->is_enabled || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return false;
            }

            $limitKey = 'autoreply:' . md5($email);
            $sentToday = (int) Cache::get($limitKey, 0);

            if ($sentToday >= self::PER_ADDRESS_PER_DAY) {
                return false;
            }
            Cache::put($limitKey, $sentToday + 1, now()->addDay());

            Mail::to($email)->queue(new CustomerMessage(
                EmailTemplates::render($template->subject, $record),
                EmailTemplates::render($template->body, $record),
                config('contact.inbox') ?: config('mail.from.address'),
                'Agarwal Brothers',
            ));

            if (method_exists($record, 'logActivity')) {
                $record->logActivity('Automatic reply sent to the customer');
            }

            return true;
        } catch (\Throwable $e) {
            report($e);   // a failed auto-reply must never break the form

            return false;
        }
    }
}
