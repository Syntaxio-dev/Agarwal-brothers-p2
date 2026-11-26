<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Which parts of the web desk (sales sub-domain, /rep) a rep may use, chosen by a manager per rep (Sales team > Reps > profile > Web desk).
 * A JSON list of area keys (App\Sales\Rep\Support\RepDesk::AREAS). Null or empty = no web desk at all: the rep cannot sign in there.
 * Reps that exist now already had the desk with Leads and Enquiries (phase 1, 4 Nov 2026): they keep exactly that, nothing more.
 * Additive: the phone app and the sync do not read this column.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('sales_profiles', 'desk_areas')) {
            Schema::table('sales_profiles', function (Blueprint $table) {
                $table->json('desk_areas')->nullable()->after('is_active');
            });

            DB::table('sales_profiles')->update(['desk_areas' => json_encode(['leads'])]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('sales_profiles', 'desk_areas')) {
            Schema::table('sales_profiles', function (Blueprint $table) {
                $table->dropColumn('desk_areas');
            });
        }
    }
};
