<?php

namespace App\Sales\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/** One file of a task, on the private disk (only the assigned rep and the managers can fetch it, through an authorised request). */
class SalesTaskFile extends Model
{
    public const MAX_KB = 5120;   // 5 MB, like a bill photo

    /** extension => mime. A file is accepted by its EXTENSION and its real content (see typeOf). */
    public const TYPES = [
        'pdf' => 'application/pdf',
        'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'xls' => 'application/vnd.ms-excel',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'doc' => 'application/msword',
        'csv' => 'text/csv',
        'txt' => 'text/plain',
    ];

    protected $table = 'sales_task_files';

    protected $fillable = ['task_id', 'name', 'mime', 'size', 'path'];

    protected static function booted(): void
    {
        static::deleting(fn (SalesTaskFile $file) => Storage::disk('local')->delete($file->path));
    }

    /**
     * The mime of a file the manager uploads, or null when it is not one of the allowed kinds. The name's extension must be allowed AND the first
     * bytes must look like that kind (a program renamed to .pdf is refused).
     */
    public static function typeOf(string $name, string $bytes): ?string
    {
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $mime = self::TYPES[$ext] ?? null;
        if ($mime === null || $bytes === '') {
            return null;
        }

        $head = substr($bytes, 0, 8);
        $ok = match ($ext) {
            'pdf' => str_starts_with($head, '%PDF'),
            'jpg', 'jpeg' => str_starts_with($head, "\xFF\xD8\xFF"),
            'png' => str_starts_with($head, "\x89PNG"),
            'webp' => str_starts_with($head, 'RIFF') && substr($bytes, 8, 4) === 'WEBP',
            'xlsx', 'docx' => str_starts_with($head, 'PK'),                          // the Office formats are zip files
            'xls', 'doc' => str_starts_with($head, "\xD0\xCF\x11\xE0") || str_starts_with($head, 'PK'),
            'csv', 'txt' => ! str_contains(substr($bytes, 0, 2000), "\0"),            // text has no NUL bytes
            default => false,
        };

        return $ok ? $mime : null;
    }

    /** A name that is safe to show and to hand to a phone: no path, no control characters. */
    public static function cleanName(string $name): string
    {
        $name = basename(str_replace('\\', '/', $name));
        $name = preg_replace('/[\x00-\x1F\x7F<>:"|?*]/u', '', $name) ?? '';

        $name = mb_substr(trim($name), 0, 120);

        // nothing left, or only dots ("..") is no name
        return $name === '' || preg_match('/^\.+$/', $name) ? 'file' : $name;
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(SalesTask::class, 'task_id');
    }
}
