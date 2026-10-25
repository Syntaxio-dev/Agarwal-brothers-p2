<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The product guide's "before the meeting" fields (who usually buys it, a 20-second pitch, key notes, do / avoid, if-then
 * answers) and the reps' own scripts. Additive: nothing existing changes.
 *  - sales_products: client_types, pitch, key_notes, formula, do_list, avoid_list, match_rules (all empty until the manager writes them;
 *    client_types gets a starting suggestion for the products already in the guide).
 *  - sales_scripts: a rep's own words for a product ("my pitch for weighing balances"). Private to the rep; managers can read them.
 */
return new class extends Migration
{
    /** Starting suggestion only (the manager changes it in the admin): client type => product names of the guide. */
    private const START = [
        'pharma_qc' => ['Lab Weighing Balance', 'Moisture Balances/ Moisture Analyzers', 'Portable/ Benchtop/ Pocket pH Meters', 'Automatic Potentiometric Titrators', 'Automatic Karl Fischer Volumetric Titrators', 'UV Visible Spectrophotometer', 'Tablet Disintegration Tester', 'Tablet Dissolution Test Apparatus', 'Tablet Hardness Tester', 'Tablet Friability Tester', 'Stability Chambers'],
        'hospital' => ['Laboratory Refrigerator', 'Deep Freezer (-40) Vertical', 'Ultra Low Freezers (-86 C)', 'Biosafety Cabinet', 'Laminar Flow Cabinet', 'Micro Centrifuge', 'Hematocrit Centrifuge', 'Refrigerated Centrifuge', 'Laboratory Autoclave', 'Bacteriological Incubator', 'Pipettes', 'Vortex Mixer'],
        'university' => ['Pipettes', 'Micro Centrifuge', 'Refrigerated Centrifuge', 'Magnetic Hotplate Stirrers', 'Vortex Mixer', 'Ultrasonic Homogenizer/Probe Sonicator', 'Thermostatic Water Bath', 'UV Visible Spectrophotometer', 'Fume Hood', 'Lab Weighing Balance', 'Lab Water Purification Systems'],
        'food_dairy' => ['Kjeldahl Nitrogen/Protein Analyzer System', 'Fat/Soxhlet Analyzer', 'Crude Fiber Analyzer', 'Moisture Balances/ Moisture Analyzers', 'Lab Weighing Balance', 'Cooking Oil Tester', 'Thermostatic Water Bath', 'Portable/ Benchtop/ Pocket pH Meters', 'Bacteriological Incubator', 'Laboratory Autoclave', 'Viscometer'],
        'industrial_qc' => ['Lab Weighing Balance', 'Viscometer', 'Moisture Balances/ Moisture Analyzers', 'Digital Polarimeter', 'Visual Melting Point Apparatus', 'Portable/ Benchtop/ Pocket pH Meters', 'Portable/ Benchtop/ Pocket EC/Resistivity Meters', 'Paint Mixing Balances', 'Tap/Bulk Density Analyzer', 'Leak Tester', 'Rotary Evaporator'],
        'water_environment' => ['Portable/ Benchtop/ Pocket pH Meters', 'Portable/ Benchtop/ Pocket EC/Resistivity Meters', 'Portable/ Benchtop/ Pocket DO Meters', 'Benchtop/ Handheld/ Pocket TDS Meters', 'Multiparameter Meters pH/EC/TDS/Salinity/ORP/DO', 'Online / Process pH/EC/TDS/DO/Turbidity/ORP/Salinity Meters', 'Benchtop/ Handheld/ Pocket Salinity Meters', 'Portable/ Benchtop/ Pocket ORP Meters', 'BOD Incubator', 'Lab Water Purification Systems', 'Lab Weighing Balance'],
    ];

    public function up(): void
    {
        Schema::table('sales_products', function (Blueprint $table) {
            if (! Schema::hasColumn('sales_products', 'client_types')) {
                $table->json('client_types')->nullable();   // ['pharma_qc', ...] who usually buys it
                $table->text('pitch')->nullable();          // the 20-second pitch; **word** is highlighted on the phone
                $table->json('key_notes')->nullable();      // up to 5 short lines for the cheat sheet
                $table->string('formula', 300)->nullable();
                $table->json('do_list')->nullable();        // up to 3 short lines
                $table->json('avoid_list')->nullable();     // up to 3 short lines
                $table->json('match_rules')->nullable();    // [{if, then}] up to 4
            }
        });

        if (! Schema::hasTable('sales_scripts')) {
            Schema::create('sales_scripts', function (Blueprint $table) {
                $table->id();
                $table->char('uuid', 36)->unique();
                $table->foreignId('user_id')->constrained()->restrictOnDelete();
                $table->char('product_uuid', 36)->nullable()->index();   // not a foreign key: the guide may change
                $table->string('product_name', 200);                      // a copy, so the script stays readable
                $table->string('title', 120)->nullable();
                $table->text('body');
                $table->dateTime('client_updated_at', 3);
                $table->timestamps();
                $table->softDeletes();

                $table->index(['user_id', 'updated_at']);
            });
        }

        // a starting suggestion, only for products nobody has tagged yet (a fresh database has no products: nothing happens)
        $tags = [];
        foreach (self::START as $type => $names) {
            foreach ($names as $name) {
                $tags[$name][] = $type;
            }
        }
        foreach ($tags as $name => $types) {
            DB::table('sales_products')->where('name', $name)->whereNull('client_types')->update(['client_types' => json_encode($types)]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_scripts');

        Schema::table('sales_products', function (Blueprint $table) {
            $table->dropColumn(['client_types', 'pitch', 'key_notes', 'formula', 'do_list', 'avoid_list', 'match_rules']);
        });
    }
};
