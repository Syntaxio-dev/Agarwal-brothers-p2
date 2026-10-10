<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Writes the admin activity log. Only actions by a signed-in person are recorded (visitors filling in forms,
 * seeders and console commands are not), and a logging problem never stops the real save.
 */
class ActivityLogger
{
    /** Never shown, not even as "old" and "new": only "changed". */
    public const SECRET = ['password', 'remember_token'];

    /** Not worth a log line: bookkeeping and drag-and-drop ordering. */
    public const IGNORE = ['id', 'created_at', 'updated_at', 'sort_order', 'top_pick_order'];

    public static function record(string $action, ?Model $subject = null, array $changes = [], ?User $user = null): void
    {
        try {
            $user ??= auth()->user();

            if (! $user) {
                return;
            }

            ActivityLog::create([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'action' => $action,
                'subject_type' => $subject ? $subject::class : null,
                'subject_id' => $subject?->getKey(),
                'subject_label' => $subject ? static::label($subject) : null,
                'details' => $changes ?: null,
                'ip_address' => request()?->ip(),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /** @return array<string, array{old: ?string, new: ?string}> what changed in the last save, ready to store */
    public static function diff(Model $model): array
    {
        $out = [];

        foreach ($model->getChanges() as $field => $new) {
            if (in_array($field, self::IGNORE, true)) {
                continue;
            }

            if (in_array($field, self::SECRET, true)) {
                $out[$field] = ['old' => null, 'new' => '(changed)'];

                continue;
            }

            $isFlag = $model->hasCast($field, ['bool', 'boolean']);
            $out[$field] = [
                'old' => static::show($model->getRawOriginal($field), $isFlag),
                'new' => static::show($new, $isFlag),
            ];
        }

        return $out;
    }

    /** A readable name for the record: its name, title, e-mail... */
    public static function label(Model $model): string
    {
        foreach (['name', 'title', 'email', 'key'] as $field) {
            $value = $model->getAttribute($field);

            if (filled($value) && is_scalar($value)) {
                return Str::limit(trim((string) $value), 120);
            }
        }

        return Str::headline(class_basename($model)) . ' #' . $model->getKey();
    }

    private static function show(mixed $value, bool $flag = false): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if ($flag) {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'Yes' : 'No';
        }
        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }
        if (is_array($value)) {
            return '(list updated)';
        }

        $text = trim((string) $value);

        // Lists and settings are stored as JSON text: say they changed instead of dumping them.
        if ($text !== '' && in_array($text[0], ['[', '{'], true) && json_decode($text) !== null) {
            return '(list updated)';
        }

        return Str::limit($text, 160);
    }
}
