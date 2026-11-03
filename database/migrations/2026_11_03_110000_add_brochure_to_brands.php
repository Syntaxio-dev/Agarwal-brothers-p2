<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('brands', 'brochure')) {
            Schema::table('brands', function (Blueprint $table) {
                $table->string('brochure')->nullable()->after('logo');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('brands', 'brochure')) {
            Schema::table('brands', function (Blueprint $table) {
                $table->dropColumn('brochure');
            });
        }
    }
};
