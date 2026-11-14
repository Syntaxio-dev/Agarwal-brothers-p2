<?php

namespace App\Console\Commands;

use App\Models\Brand;
use App\Models\Client;
use App\Support\ImageOptimizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Cuts the empty border out of the logos that are already stored (brands and clients), so every logo fills its box alike.
 * New uploads are trimmed automatically; this is for the ones uploaded before. SVG logos are left alone.
 * Without --apply it only reports. With --apply each trimmed logo is saved as a new WebP file and the old file is removed.
 */
class LogosTrim extends Command
{
    protected $signature = 'logos:trim {--apply : Actually replace the logos (default is a dry run)}';

    protected $description = 'Trim the empty border around stored brand and client logos (dry run unless --apply)';

    public function handle(): int
    {
        if (! ImageOptimizer::available()) {
            $this->error('PHP GD with WebP support is not enabled, so logos cannot be processed on this server.');

            return self::FAILURE;
        }

        $apply = (bool) $this->option('apply');
        $disk = Storage::disk('public');
        $rows = [];
        $changed = 0;

        foreach ([['Brand', Brand::class, 'brands'], ['Client', Client::class, 'clients']] as [$label, $model, $folder]) {
            foreach ($model::whereNotNull('logo')->where('logo', '!=', '')->orderBy('name')->get() as $record) {
                $path = $record->logo;
                $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

                if ($ext === 'svg' || ! $disk->exists($path)) {
                    $rows[] = [$label, $record->name, $ext === 'svg' ? 'SVG (left as it is)' : 'file missing'];

                    continue;
                }

                $full = $disk->path($path);
                [$w, $h] = @getimagesize($full) ?: [0, 0];
                $bytes = ImageOptimizer::trimmedLogoWebp($full);

                if ($bytes === null) {
                    $rows[] = [$label, $record->name, "already tight ({$w}x{$h})"];

                    continue;
                }

                $size = @getimagesizefromstring($bytes) ?: [0, 0];
                $rows[] = [$label, $record->name, "{$w}x{$h} -> {$size[0]}x{$size[1]}" . ($apply ? ' (replaced)' : '')];
                $changed++;

                if ($apply) {
                    $new = trim($folder . '/' . Str::ulid() . '.webp', '/');
                    $disk->put($new, $bytes, 'public');
                    $record->update(['logo' => $new]);

                    // delete the old file only when nothing else points to it
                    if (! Brand::where('logo', $path)->exists() && ! Client::where('logo', $path)->exists()) {
                        $disk->delete($path);
                    }
                }
            }
        }

        $this->table(['Type', 'Name', 'Result'], $rows);
        $this->info(($apply ? 'Trimmed ' : 'Would trim ') . $changed . ' logo(s).' . ($apply ? '' : ' Run again with --apply to do it.'));

        return self::SUCCESS;
    }
}
