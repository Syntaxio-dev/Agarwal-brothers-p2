<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/** One line of the admin activity log: who did what, when. Written by App\Support\ActivityLogger, never edited. */
class ActivityLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['user_id', 'user_name', 'action', 'subject_type', 'subject_id', 'subject_label', 'details', 'ip_address'];

    protected $casts = ['details' => 'array'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** "Product", "Team Member"... */
    public function typeLabel(): string
    {
        return $this->subject_type ? Str::headline(class_basename($this->subject_type)) : '';
    }

    /** "short_description" becomes "Short Description"; yes/no switches lose their "is_" ("is_active" becomes "Active"). */
    public static function fieldLabel(string $field): string
    {
        return Str::headline(Str::after($field, str_starts_with($field, 'is_') ? 'is_' : ''));
    }

    /** "Signed in", "Created", "Updated", "Deleted" */
    public function actionLabel(): string
    {
        return match ($this->action) {
            'login' => 'Signed in',
            default => ucfirst($this->action),
        };
    }

    /** Short sentence: "Name, Short description and 2 more" */
    public function summary(): string
    {
        $fields = collect(array_keys($this->details ?? []))->map(fn ($f) => static::fieldLabel($f));

        if ($this->action === 'created') {
            return 'New ' . strtolower($this->typeLabel());
        }
        if ($this->action === 'deleted') {
            return ucfirst(strtolower($this->typeLabel())) . ' removed';
        }
        if ($this->action === 'login') {
            return 'Signed in to the admin panel';
        }
        if ($fields->isEmpty()) {
            return 'Changed';
        }

        return $fields->count() <= 3
            ? $fields->implode(', ')
            : $fields->take(2)->implode(', ') . ' and ' . ($fields->count() - 2) . ' more';
    }
}
