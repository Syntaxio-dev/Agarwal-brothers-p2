<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The order of the reps in the roster, set by a manager by dragging a row (Sales team > Reps). Null = not placed yet: those reps come
 * after the placed ones, by employee code. Additive: nothing existing changes.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('sales_profiles', 'sort_order')) {
            Schema::table('sales_profiles', function (Blueprint $table) {
                $table->unsignedInteger('sort_order')->nullable()->after('is_active')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('sales_profiles', 'sort_order')) {
            Schema::table('sales_profiles', function (Blueprint $table) {
                $table->dropIndex(['sort_order']);
                $table->dropColumn('sort_order');
            });
        }
    }
};
