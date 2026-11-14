<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('brands', 'sort_order')) {
            Schema::table('brands', function (Blueprint $table) {
                // order of the brand cards on the vertical pages (drag rows in Admin > Brands); equal numbers fall back to the name
                $table->unsignedInteger('sort_order')->default(0)->after('is_active');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('brands', 'sort_order')) {
            Schema::table('brands', function (Blueprint $table) {
                $table->dropColumn('sort_order');
            });
        }
    }
};
