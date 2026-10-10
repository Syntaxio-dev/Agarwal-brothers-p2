<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One line in the internal team log of an enquiry, message or application. Never shown to customers. */
class Note extends Model
{
    public const KIND_NOTE = 'note';

    public const KIND_SYSTEM = 'system';

    protected $fillable = ['noteable_type', 'noteable_id', 'user_id', 'kind', 'body'];

    public function noteable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isSystem(): bool
    {
        return $this->kind === self::KIND_SYSTEM;
    }
}
