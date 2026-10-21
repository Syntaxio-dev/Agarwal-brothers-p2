<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** One catalogue PDF becomes a list of catalogues (title + PDF), e.g. Equipment and Consumables. The saved PDF is kept as the first entry. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->json('catalogues')->nullable()->after('catalogue_file');
        });

        foreach (DB::table('site_settings')->whereNotNull('catalogue_file')->get(['id', 'catalogue_file']) as $row) {
            DB::table('site_settings')->where('id', $row->id)->update([
                'catalogues' => json_encode([['title' => 'Product catalogue', 'file' => $row->catalogue_file]]),
            ]);
        }

        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn('catalogue_file');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('catalogue_file')->nullable()->after('id');
        });

        foreach (DB::table('site_settings')->whereNotNull('catalogues')->get(['id', 'catalogues']) as $row) {
            $first = json_decode($row->catalogues, true)[0]['file'] ?? null;
            DB::table('site_settings')->where('id', $row->id)->update(['catalogue_file' => $first]);
        }

        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn('catalogues');
        });
    }
};
