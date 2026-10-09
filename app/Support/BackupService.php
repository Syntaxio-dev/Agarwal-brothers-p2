<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use RuntimeException;
use ZipArchive;

/**
 * Pure-PHP backups (no mysqldump needed, so they also work on shared hosting).
 * Files live in storage/app/backups, which is never publicly reachable.
 */
class BackupService
{
    public const KEEP = 7;

    public static function dir(): string
    {
        $dir = storage_path('app/backups');
        if (! is_dir($dir)) {
            mkdir($dir, 0750, true);
        }

        return $dir;
    }

    /** @return array<int, array{name:string,type:string,size:int,at:int}> newest first */
    public static function list(): array
    {
        $rows = [];
        foreach (glob(static::dir() . '/*.{sql,zip}', GLOB_BRACE) ?: [] as $file) {
            $name = basename($file);
            $rows[] = [
                'name' => $name,
                'type' => str_starts_with($name, 'full-') ? 'Full backup' : (str_starts_with($name, 'database-') ? 'Database' : 'Uploads'),
                'size' => (int) filesize($file),
                'at' => (int) filemtime($file),
            ];
        }
        usort($rows, fn ($a, $b) => $b['at'] <=> $a['at']);

        return $rows;
    }

    /** Only plain names that this service created; blocks path tricks. */
    public static function path(string $name): ?string
    {
        if (! preg_match('/^(full|database|uploads)-\d{8}-\d{6}\.(sql|zip)$/', $name)) {
            return null;
        }
        $path = static::dir() . '/' . $name;

        return is_file($path) ? $path : null;
    }

    public static function delete(string $name): bool
    {
        $path = static::path($name);

        return $path ? @unlink($path) : false;
    }

    public static function database(): string
    {
        $path = static::dir() . '/database-' . now()->format('Ymd-His') . '.sql';
        static::dumpTo($path);

        return $path;
    }

    public static function uploads(): string
    {
        $path = static::dir() . '/uploads-' . now()->format('Ymd-His') . '.zip';
        $zip = static::openZip($path);
        static::addFolders($zip);
        $zip->close();

        return $path;
    }

    public static function full(): string
    {
        $sql = tempnam(sys_get_temp_dir(), 'abdb');
        try {
            static::dumpTo($sql);
            $path = static::dir() . '/full-' . now()->format('Ymd-His') . '.zip';
            $zip = static::openZip($path);
            $zip->addFile($sql, 'database.sql');
            static::addFolders($zip);
            $zip->close();   // the temp .sql must still exist until here
        } finally {
            @unlink($sql);
        }

        return $path;
    }

    /** Keep the newest self::KEEP files of each kind. */
    public static function prune(): void
    {
        $seen = [];
        foreach (static::list() as $row) {
            $seen[$row['type']] = ($seen[$row['type']] ?? 0) + 1;
            if ($seen[$row['type']] > self::KEEP) {
                static::delete($row['name']);
            }
        }
    }

    protected static function openZip(string $path): ZipArchive
    {
        $zip = new ZipArchive;
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Could not create the backup file.');
        }

        return $zip;
    }

    protected static function addFolders(ZipArchive $zip): void
    {
        foreach (['public' => storage_path('app/public'), 'private' => storage_path('app/private')] as $label => $root) {
            if (! is_dir($root)) {
                continue;
            }
            $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS));
            foreach ($it as $file) {
                if ($file->isFile()) {
                    $zip->addFile($file->getPathname(), $label . '/' . ltrim(str_replace(chr(92), '/', substr($file->getPathname(), strlen($root))), '/'));
                }
            }
        }
    }

    protected static function dumpTo(string $path): void
    {
        $pdo = DB::connection()->getPdo();
        $out = fopen($path, 'wb');
        if (! $out) {
            throw new RuntimeException('Could not write the backup file.');
        }

        fwrite($out, '-- Agarwal Brothers database backup, ' . now()->toDateTimeString() . "\nSET FOREIGN_KEY_CHECKS=0;\nSET NAMES utf8mb4;\n\n");

        $tables = DB::select('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"');
        foreach ($tables as $row) {
            $table = array_values((array) $row)[0];
            $q = '`' . str_replace('`', '``', $table) . '`';

            $create = array_values((array) DB::selectOne("SHOW CREATE TABLE $q"))[1];
            fwrite($out, "DROP TABLE IF EXISTS $q;\n$create;\n\n");

            $stmt = $pdo->query("SELECT * FROM $q");
            $batch = [];
            while ($r = $stmt->fetch(\PDO::FETCH_NUM)) {
                $batch[] = '(' . implode(',', array_map(fn ($v) => $v === null ? 'NULL' : $pdo->quote((string) $v), $r)) . ')';
                if (count($batch) >= 200) {
                    fwrite($out, "INSERT INTO $q VALUES " . implode(",\n", $batch) . ";\n");
                    $batch = [];
                }
            }
            if ($batch) {
                fwrite($out, "INSERT INTO $q VALUES " . implode(",\n", $batch) . ";\n");
            }
            fwrite($out, "\n");
        }

        fwrite($out, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($out);
    }
}
