<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * "My territories" of a rep on the web desk (the app keeps its own list on the phone, `prefs.prospect_areas`): a JSON list of up to 20
 * territory names. Only the desk's Prospects screen reads it, to show the rep's areas first. Null = none chosen yet. Additive.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('sales_profiles', 'desk_territories')) {
            Schema::table('sales_profiles', function (Blueprint $table) {
                $table->json('desk_territories')->nullable()->after('desk_areas');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('sales_profiles', 'desk_territories')) {
            Schema::table('sales_profiles', function (Blueprint $table) {
                $table->dropColumn('desk_territories');
            });
        }
    }
};
