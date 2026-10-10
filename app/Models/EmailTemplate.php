<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;

/** An editable e-mail: kind "auto" is sent automatically, "reply" is a starting point for manual replies. */
class EmailTemplate extends Model
{
    use LogsActivity;
    protected $fillable = ['key', 'kind', 'name', 'subject', 'body', 'is_enabled'];

    protected $casts = ['is_enabled' => 'boolean'];

    public static function forKey(string $key): ?self
    {
        return static::where('key', $key)->first();
    }

    /** Ready-made replies people can pick when writing to a customer. */
    public static function replies()
    {
        return static::where('kind', 'reply')->where('is_enabled', true)->orderBy('id')->get();
    }

    public function isAuto(): bool
    {
        return $this->kind === 'auto';
    }
}
