<?php

namespace App\Sales\Models;

use Illuminate\Database\Eloquent\Model;

/** One entry of the shared position (job title) list. Preset ones come with the module; reps and managers can add more. */
class SalesPosition extends Model
{
    protected $table = 'sales_positions';

    protected $fillable = ['name', 'is_preset', 'created_by'];

    protected function casts(): array
    {
        return ['is_preset' => 'boolean'];
    }

    /** The names for the dropdown (the app takes the whole list on every pull: it is short). */
    public static function names(): array
    {
        return static::query()->orderBy('is_preset', 'desc')->orderBy('name')->pluck('name')->all();
    }
}
