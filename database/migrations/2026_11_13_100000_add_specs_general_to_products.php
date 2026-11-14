<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'specs_general')) {
            Schema::table('products', function (Blueprint $table) {
                // true = the specifications are ranges across a whole model line (for example Cubis), not one model
                $table->boolean('specs_general')->default(false)->after('specs');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'specs_general')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('specs_general');
            });
        }
    }
};
