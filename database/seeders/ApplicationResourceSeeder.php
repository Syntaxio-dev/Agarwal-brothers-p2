<?php

namespace Database\Seeders;

use App\Models\ApplicationResource;
use Illuminate\Database\Seeder;

class ApplicationResourceSeeder extends Seeder
{
    public function run(): void
    {
        if (ApplicationResource::exists()) {
            return;
        }

        $rows = [
            ['appnote', 'Rotary Evaporation: Solvent Recovery Best Practices', 'BUCHI · Rotary Evaporators', 'Step-by-step guide for optimising evaporation efficiency, reducing bumping and achieving maximum solvent recovery.'],
            ['appnote', 'HPLC Column Selection for Pharmaceutical Analysis', 'Shimadzu · HPLC Systems', 'Selecting the right reversed-phase column for API and impurity analysis in pharmaceutical QC labs.'],
            ['appnote', 'Good Weighing Practice (GWP) for Regulated Labs', 'Mettler Toledo · Analytical Balances', 'GLP/GMP compliant weighing routines, calibration intervals and uncertainty calculations.'],
            ['guide', 'Centrifuge Maintenance & Rotor Care Guide', 'Hettich · Centrifuges', 'Maintenance schedule, rotor inspection checklists and troubleshooting tips to maximise uptime and safety.'],
            ['guide', 'Water Purity Standards: Type I, II & III Explained', 'Thermo Fisher · Water Purification', 'Which water grade does your application need? A practical guide for HPLC, cell culture and general lab use.'],
            ['video', 'Introduction to UV-Vis Spectrophotometry', 'Shimadzu · UV-Vis', 'Recorded webinar covering Beer-Lambert law, instrument qualification and common troubleshooting.'],
            ['video', 'Best Practices in Laboratory Weighing (GLP/GMP)', 'Mettler Toledo · Balances', 'Selecting the right balance, daily routine tests and meeting GLP/GMP documentation requirements.'],
            ['brochure', 'BUCHI Product Catalogue 2025-26', 'BUCHI · Full Range', 'Rotary evaporators, spray dryers, melting point systems, freeze dryers and accessories.'],
            ['brochure', 'Shimadzu HPLC & UHPLC Systems Overview', 'Shimadzu · HPLC', 'Full HPLC portfolio with specifications and application tables.'],
        ];

        foreach ($rows as $i => [$category, $title, $source, $description]) {
            ApplicationResource::create([
                'category' => $category,
                'title' => $title,
                'source' => $source,
                'description' => $description,
                'is_active' => true,
                'sort_order' => $i,
            ]);
        }
    }
}
