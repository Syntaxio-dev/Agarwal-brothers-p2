<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->boolean('is_featured')->default(false)->after('is_active');
            $table->string('avatar')->nullable()->after('organization');
        });

        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('clients_stat_number', 20)->nullable();
            $table->string('clients_stat_label', 40)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn(['is_featured', 'avatar']);
        });

        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['clients_stat_number', 'clients_stat_label']);
        });
    }
};
