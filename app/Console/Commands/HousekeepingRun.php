<?php

namespace App\Console\Commands;

use App\Support\BackupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * Daily clean-up so the server's database and disk do not keep growing forever.
 * Removes only throw-away data: expired sessions and cache, old failed jobs, old log files,
 * abandoned upload temp files and surplus backups. Customer records are never touched.
 */
class HousekeepingRun extends Command
{
    protected $signature = 'housekeeping:run';

    protected $description = 'Remove expired sessions and cache, old failed jobs, old logs, abandoned temp uploads and surplus backups';

    private const LOG_KEEP_DAYS = 14;

    private const LOG_ROTATE_MB = 20;

    private const ACTIVITY_KEEP_DAYS = 180;

    private const TEMP_UPLOAD_HOURS = 24;

    private const FAILED_JOB_HOURS = 336;   // 14 days

    public function handle(): int
    {
        $rows = [
            ['Expired sessions', $this->sessions()],
            ['Expired cache entries', $this->cache()],
            ['Old failed jobs', $this->failedJobs()],
            ['Old log files', $this->logs()],
            ['Abandoned upload temp files', $this->tempUploads()],
            ['Surplus backups', $this->backups()],
            ['Old activity log lines', $this->activityLog()],
        ];

        $this->table(['Cleaned', 'Removed'], $rows);

        return self::SUCCESS;
    }

    private function sessions(): int
    {
        if (config('session.driver') !== 'database' || ! Schema::hasTable(config('session.table', 'sessions'))) {
            return 0;
        }

        $cutoff = now()->subMinutes((int) config('session.lifetime'))->getTimestamp();

        return DB::table(config('session.table', 'sessions'))->where('last_activity', '<', $cutoff)->delete();
    }

    private function cache(): int
    {
        if (config('cache.default') !== 'database') {
            return 0;
        }

        $table = config('cache.stores.database.table', 'cache');
        $removed = 0;

        if (Schema::hasTable($table)) {
            $removed += DB::table($table)->where('expiration', '<', time())->delete();
        }

        $locks = config('cache.stores.database.lock_table', 'cache_locks');
        if (Schema::hasTable($locks)) {
            $removed += DB::table($locks)->where('expiration', '<', time())->delete();
        }

        return $removed;
    }

    private function failedJobs(): int
    {
        $table = config('queue.failed.table', 'failed_jobs');

        if (! Schema::hasTable($table)) {
            return 0;
        }

        $before = DB::table($table)->count();
        Artisan::call('queue:prune-failed', ['--hours' => self::FAILED_JOB_HOURS]);

        return $before - DB::table($table)->count();
    }

    /** Roll an oversized laravel.log over to a dated file, then delete log files past the keep period. */
    private function logs(): int
    {
        $dir = storage_path('logs');
        if (! is_dir($dir)) {
            return 0;
        }

        $current = $dir . '/laravel.log';
        if (is_file($current) && filesize($current) > self::LOG_ROTATE_MB * 1024 * 1024) {
            @rename($current, $dir . '/laravel-' . now()->format('Ymd-His') . '.log');
        }

        $removed = 0;
        foreach (File::glob($dir . '/*.log') as $file) {
            if (basename($file) !== 'laravel.log' && filemtime($file) < now()->subDays(self::LOG_KEEP_DAYS)->getTimestamp()) {
                $removed += @unlink($file) ? 1 : 0;
            }
        }

        return $removed;
    }

    /** Files Livewire / Filament leave behind when an upload is started but the form is never saved. */
    private function tempUploads(): int
    {
        $disk = config('livewire.temporary_file_upload.disk') ?: config('filesystems.default');
        $dir = trim((string) (config('livewire.temporary_file_upload.directory') ?: 'livewire-tmp'), '/');

        $storage = Storage::disk($disk);
        if (! $storage->exists($dir)) {
            return 0;
        }

        $cutoff = now()->subHours(self::TEMP_UPLOAD_HOURS)->getTimestamp();
        $removed = 0;

        foreach ($storage->files($dir) as $file) {
            if ($storage->lastModified($file) < $cutoff) {
                $storage->delete($file);
                $removed++;
            }
        }

        return $removed;
    }

    /** The activity log keeps six months; older lines are removed. */
    private function activityLog(): int
    {
        if (! Schema::hasTable('activity_logs')) {
            return 0;
        }

        return DB::table('activity_logs')->where('created_at', '<', now()->subDays(self::ACTIVITY_KEEP_DAYS))->delete();
    }

    private function backups(): int
    {
        $before = count(BackupService::list());
        BackupService::prune();

        return $before - count(BackupService::list());
    }
}
