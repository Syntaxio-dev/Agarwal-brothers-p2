<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('categories', 'short_description')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->string('short_description', 160)->nullable()->after('description');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('categories', 'short_description')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropColumn('short_description');
            });
        }
    }
};
