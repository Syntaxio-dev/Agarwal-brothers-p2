<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sales module: every expense now says where the work was (local = in Jaipur, outstation = outside) and whether there is a bill.
 * Both are filled by the phone app from version 1.6; older rows and older phones leave them empty (null). Additive only.
 * The two new categories (parking, gifting) need no change here: `category` is plain text.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_expenses', function (Blueprint $table) {
            if (! Schema::hasColumn('sales_expenses', 'work_area')) {
                $table->string('work_area', 12)->nullable()->after('note')->index();
            }
            if (! Schema::hasColumn('sales_expenses', 'bill_type')) {
                $table->string('bill_type', 12)->nullable()->after('work_area')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales_expenses', function (Blueprint $table) {
            $table->dropColumn(['work_area', 'bill_type']);
        });
    }
};
