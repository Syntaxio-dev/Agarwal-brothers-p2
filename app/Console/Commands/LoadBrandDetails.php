<?php

namespace App\Console\Commands;

use App\Models\Brand;
use App\Models\Country;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LoadBrandDetails extends Command
{
    protected $signature = 'brands:details {--overwrite : also replace a logo/description/country a brand already has}';

    protected $description = 'Fill brand country, description and logo from database/data/brand_details.php (researched data). Safe to run again.';

    /** Country coordinates for any country not already in the table (for the world map). */
    private array $coords = [
        'Austria' => [47.5162, 14.5501], 'Belgium' => [50.5039, 4.4699], 'Canada' => [56.1304, -106.3468],
        'China' => [35.8617, 104.1954], 'Denmark' => [56.2639, 9.5018], 'Finland' => [61.9241, 25.7482],
        'France' => [46.2276, 2.2137], 'Germany' => [51.1657, 10.4515], 'India' => [20.5937, 78.9629],
        'Ireland' => [53.1424, -7.6921], 'Italy' => [41.8719, 12.5674], 'Japan' => [36.2048, 138.2529],
        'Netherlands' => [52.1326, 5.2913], 'Norway' => [60.4720, 8.4689], 'Poland' => [51.9194, 19.1451],
        'Singapore' => [1.3521, 103.8198], 'South Korea' => [35.9078, 127.7669], 'Spain' => [40.4637, -3.7492],
        'Sweden' => [60.1282, 18.6435], 'Switzerland' => [46.8182, 8.2275], 'Taiwan' => [23.6978, 120.9605],
        'Ukraine' => [48.3794, 31.1656], 'United Kingdom' => [55.3781, -3.4360], 'United States' => [39.8283, -98.5795],
    ];

    /** Normalise the country names the research may use to the ones we store. */
    private array $alias = [
        'usa' => 'United States', 'us' => 'United States', 'u.s.' => 'United States', 'u.s.a.' => 'United States',
        'united states of america' => 'United States', 'america' => 'United States',
        'uk' => 'United Kingdom', 'u.k.' => 'United Kingdom', 'england' => 'United Kingdom', 'britain' => 'United Kingdom',
        'great britain' => 'United Kingdom', 'korea' => 'South Korea', 'republic of korea' => 'South Korea',
        'the netherlands' => 'Netherlands', 'holland' => 'Netherlands',
    ];

    public function handle(): int
    {
        $overwrite = (bool) $this->option('overwrite');
        $data = require database_path('data/brand_details.php');
        $logoDir = database_path('data/brand-logos');
        $countries = [];   // name => Country, cached
        $filled = 0;

        foreach ($data as $name => $row) {
            $brand = Brand::where('name', $name)->orWhere('slug', Str::slug($name))->first();
            if (! $brand) {
                $this->warn("No brand named \"{$name}\" - skipped.");
                continue;
            }

            // country
            if (! empty($row['country']) && ($overwrite || ! $brand->country_id)) {
                $country = $this->country($row['country'], $countries);
                if ($country) {
                    $brand->country_id = $country->id;
                }
            }

            // description
            if (! empty($row['description']) && ($overwrite || blank($brand->description))) {
                $brand->description = $row['description'];
            }

            // logo: copy database/data/brand-logos/<file> into storage/app/public/brands/
            if (! empty($row['logo']) && ($overwrite || blank($brand->logo))) {
                $src = $logoDir . '/' . $row['logo'];
                if (is_file($src) && ! static::safeLogo($src)) {
                    $this->warn("Logo for {$name} skipped: the SVG contains script, event handlers or external references.");
                } elseif (is_file($src)) {
                    $ext = strtolower(pathinfo($row['logo'], PATHINFO_EXTENSION)) ?: 'png';
                    $path = 'brands/' . $brand->slug . '.' . $ext;
                    Storage::disk('public')->put($path, file_get_contents($src), 'public');
                    $brand->logo = $path;
                } else {
                    $this->warn("Logo file missing for {$name}: {$row['logo']}");
                }
            }

            if ($brand->isDirty()) {
                $brand->save();
                $filled++;
            }
        }

        $this->info("Updated {$filled} brand(s). Brands with a logo: " . Brand::whereNotNull('logo')->where('logo', '!=', '')->count() . ' / ' . Brand::count());

        return self::SUCCESS;
    }

    private function country(string $name, array &$cache): ?Country
    {
        $name = trim($name);
        $norm = $this->alias[strtolower($name)] ?? $name;

        if (isset($cache[$norm])) {
            return $cache[$norm];
        }

        $country = Country::where('name', $norm)->first();
        if (! $country) {
            $c = $this->coords[$norm] ?? null;
            $country = Country::create(['name' => $norm, 'latitude' => $c[0] ?? null, 'longitude' => $c[1] ?? null]);
        }

        return $cache[$norm] = $country;
    }

    /** An SVG is only copied when it cannot run script (other image types are always fine). */
    public static function safeLogo(string $path): bool
    {
        if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'svg') {
            return true;
        }

        return ! preg_match('/<script|\son[a-z]+\s*=|javascript:|<foreignObject|<!ENTITY|xlink:href\s*=\s*["\']?https?:/i', (string) file_get_contents($path));
    }
}
