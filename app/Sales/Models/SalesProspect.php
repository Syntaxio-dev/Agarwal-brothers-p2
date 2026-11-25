<?php

namespace App\Sales\Models;

use App\Models\User;
use App\Sales\Models\Concerns\HasPhoneTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * A company that does not work with us yet (prospecting, app v1.19). Shared by the whole sales team: every rep reads it, any rep adds one,
 * only the rep who added it changes its details (a manager can in the admin). Calls to it are SalesProspectNote rows, made by any rep.
 */
class SalesProspect extends Model
{
    use HasPhoneTime, SoftDeletes;

    /** Edits made in the admin get a new phone time, so the phones take them (see HasPhoneTime). */
    protected bool $bumpsPhoneTimeOnEdit = true;

    public const SOURCES = [
        'google' => 'Google', 'linkedin' => 'LinkedIn', 'indiamart' => 'IndiaMART', 'directory' => 'Directory or website',
        'visit' => 'Seen while visiting', 'reference' => 'Reference', 'other' => 'Other',
    ];

    protected $table = 'sales_prospects';

    protected $fillable = ['uuid', 'user_id', 'updated_by', 'name', 'territory', 'address', 'source', 'notes', 'client_updated_at'];

    /** "Bagru", "bagru " and "BAGRU." are one: letters and digits only, lower case (the same idea as the app's territoryKey). */
    public static function keyOf(?string $text): string
    {
        return preg_replace('/[^\p{L}\p{N}]/u', '', Str::lower((string) $text)) ?? '';
    }

    protected static function booted(): void
    {
        static::creating(function (SalesProspect $p) {
            $p->uuid ??= (string) Str::uuid();
            $p->user_id ??= auth()->id();
            $p->client_updated_at ??= now();
        });

        static::saving(function (SalesProspect $p) {
            $p->name_key = self::keyOf($p->name);
            $p->territory_key = self::keyOf($p->territory);
            $p->updated_by = $p->isDirty('updated_by') ? $p->updated_by : (auth()->id() ?? $p->updated_by);
        });
    }

    /** The newest call decides where the prospect stands. */
    public static function statusOf(?SalesProspectNote $last): string
    {
        return $last?->outcome ?? 'new';
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function people(): HasMany
    {
        return $this->hasMany(SalesProspectPerson::class, 'prospect_id')->orderBy('id');
    }

    public function calls(): HasMany
    {
        return $this->hasMany(SalesProspectNote::class, 'prospect_uuid', 'uuid')->orderByDesc('noted_at');
    }
}
