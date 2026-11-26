<?php

namespace App\Sales\Rep\Support;

use Closure;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Illuminate\Validation\ValidationException;

/** A desk write from a screen: a refusal (the office's rule) is shown as a red message and the form stays open with what was typed. */
final class DeskUi
{
    public static function attempt(Closure $write, ?string $success = null): mixed
    {
        try {
            $result = $write();
        } catch (ValidationException $e) {
            Notification::make()->title('Not saved')->body(collect($e->errors())->flatten()->first() ?? 'This could not be saved.')->danger()->send();

            throw new Halt;
        }

        if ($success !== null) {
            Notification::make()->title($success)->success()->send();
        }

        return $result;
    }
}
