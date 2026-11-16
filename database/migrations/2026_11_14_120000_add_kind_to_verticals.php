<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A vertical is either "equipment" (everything we have today) or "consumables".
     * Every existing vertical stays equipment.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('verticals', 'kind')) {
            Schema::table('verticals', function (Blueprint $table) {
                $table->string('kind', 20)->default('equipment')->after('slug')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('verticals', 'kind')) {
            Schema::table('verticals', function (Blueprint $table) {
                $table->dropIndex(['kind']);
                $table->dropColumn('kind');
            });
        }
    }
};
