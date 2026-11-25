<?php

namespace App\Sales\Models;

use App\Sales\Support\PhoneNumber;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A person at a prospect. Replaced as a whole when the prospect is saved from a phone. */
class SalesProspectPerson extends Model
{
    protected $table = 'sales_prospect_people';

    protected $fillable = ['prospect_id', 'name', 'position', 'phone', 'email', 'linkedin'];

    protected static function booted(): void
    {
        static::saving(function (SalesProspectPerson $person) {
            $person->phone_normalized = PhoneNumber::normalize($person->phone);
        });
    }

    /** Only a plain https linkedin.com address: it is shown as a link in the admin, so nothing else may ever be stored. */
    public static function cleanLinkedin(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '' || strlen($value) > 300 || preg_match('/[\s<>"\'\\\\]/', $value)) {
            return null;
        }

        $parts = parse_url(preg_match('/^[a-z][a-z0-9+.\-]*:/i', $value) ? $value : 'https://' . $value);
        $host = strtolower((string) ($parts['host'] ?? ''));
        $path = rtrim((string) ($parts['path'] ?? ''), '/');

        if (($parts['scheme'] ?? '') !== 'https' || isset($parts['user']) || isset($parts['pass']) || isset($parts['port'])
            || ! ($host === 'linkedin.com' || str_ends_with($host, '.linkedin.com')) || strlen($path) < 2) {
            return null;
        }

        $clean = 'https://' . $host . $path;

        return strlen($clean) <= 200 ? $clean : null;
    }

    public function prospect(): BelongsTo
    {
        return $this->belongsTo(SalesProspect::class, 'prospect_id');
    }
}
