<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Insight;
use Illuminate\Database\Seeder;

class WebinarSeeder extends Seeder
{
    public function run(): void
    {
        $brand = fn (string $name) => Brand::whereRaw('LOWER(name) = ?', [strtolower($name)])->value('id');
        $at = fn (int $days, string $time) => now(Insight::TZ)->addDays($days)->setTimeFromTimeString($time)->format('Y-m-d H:i:s');

        $rows = [
            ['slug' => 'sample-lc-ms-high-throughput-metabolomics', 'brand' => 'Shimadzu',
             'title' => 'LC-MS for High-Throughput Metabolomics', 'starts_at' => $at(4, '20:30'), 'tbd' => false,
             'excerpt' => 'Discover how solid-core particle technology and high-throughput LC-MS can improve chromatographic performance for challenging polar metabolites.'],
            ['slug' => 'sample-charged-aerosol-detection-lnps', 'brand' => 'Shimadzu',
             'title' => 'Charged Aerosol Detection for LNPs', 'starts_at' => $at(5, '20:30'), 'tbd' => false,
             'excerpt' => 'Learn how charged aerosol detection, optimized chromatography and integrated workflows support reliable LNP composition analysis.'],
            ['slug' => 'sample-good-weighing-practice-regulated-labs', 'brand' => 'Mettler Toledo',
             'title' => 'Good Weighing Practice for Regulated Labs', 'starts_at' => $at(11, '00:00'), 'tbd' => true,
             'excerpt' => 'A practical look at routine tests, calibration schedules and documentation expectations for GLP and GMP environments.'],
            ['slug' => 'sample-water-purity-for-hplc', 'brand' => 'Thermo fisher',
             'title' => 'Water Purity for HPLC and Cell Culture', 'starts_at' => $at(-12, '19:30'), 'tbd' => false,
             'excerpt' => 'Choosing the right water grade and keeping it consistent across your lab.',
             'recording' => 'https://example.com/recording'],
        ];

        foreach ($rows as $row) {
            Insight::firstOrCreate(
                ['slug' => $row['slug']],
                [
                    'type' => 'webinar',
                    'brand_id' => $brand($row['brand']),
                    'title' => $row['title'],
                    'excerpt' => $row['excerpt'],
                    'starts_at' => $row['starts_at'],
                    'time_tbd' => $row['tbd'],
                    'venue' => 'Online',
                    'registration_url' => 'https://example.com/register',
                    'recording_url' => $row['recording'] ?? null,
                    'is_active' => true,
                ]
            );
        }
    }
}
