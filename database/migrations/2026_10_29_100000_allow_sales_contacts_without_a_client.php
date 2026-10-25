<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A person can be saved without a company (a customer who buys on their own). The phone sends such a contact with
 * client_uuid null. Additive: every existing row keeps its client.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_contacts', function (Blueprint $table) {
            $table->char('client_uuid', 36)->nullable()->change();
        });
    }

    public function down(): void
    {
        // people without a company cannot be given one; they are left out of the old shape
        DB::table('sales_contacts')->whereNull('client_uuid')->delete();

        Schema::table('sales_contacts', function (Blueprint $table) {
            $table->char('client_uuid', 36)->nullable(false)->change();
        });
    }
};
