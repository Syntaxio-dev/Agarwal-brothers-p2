<?php

namespace App\Console\Commands;

use App\Support\ImageOptimizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Lightens images that are already stored (uploaded before automatic WebP conversion existed).
 * Files keep their names and formats, so nothing in the database changes. Without --apply it only reports.
 */
class ImagesOptimize extends Command
{
    protected $signature = 'images:optimize {--apply : Actually rewrite the files (default is a dry run)} {--min-kb=300 : Only look at files larger than this}';

    protected $description = 'Re-save large stored JPEG/PNG images lighter without visible quality loss (dry run unless --apply)';

    public function handle(): int
    {
        if (! ImageOptimizer::available()) {
            $this->error('PHP GD with WebP support is not enabled, so images cannot be processed on this server.');

            return self::FAILURE;
        }

        $apply = (bool) $this->option('apply');
        $min = (int) $this->option('min-kb') * 1024;
        $rows = [];
        $saved = 0;

        foreach (File::allFiles(storage_path('app/public')) as $file) {
            $ext = strtolower($file->getExtension());
            if (! in_array($ext, ['jpg', 'jpeg', 'png'], true) || $file->getSize() < $min) {
                continue;
            }

            $bytes = ImageOptimizer::sameFormat($file->getPathname());
            if ($bytes === null || strlen($bytes) >= $file->getSize()) {
                continue;
            }

            $gain = $file->getSize() - strlen($bytes);
            $saved += $gain;
            $rows[] = [$file->getRelativePathname(), $this->kb($file->getSize()), $this->kb(strlen($bytes))];

            if ($apply) {
                File::put($file->getPathname(), $bytes);
            }
        }

        if ($rows) {
            $this->table(['File', 'Before', $apply ? 'After' : 'After (if applied)'], $rows);
        }

        $this->info(($apply ? 'Saved ' : 'Would save ') . $this->kb($saved) . ' across ' . count($rows) . ' files.' . ($apply ? '' : ' Run again with --apply to do it.'));

        return self::SUCCESS;
    }

    private function kb(int $bytes): string
    {
        return number_format($bytes / 1024, 0) . ' KB';
    }
}
