<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('team_members', function (Blueprint $table) {
            $table->boolean('is_leader')->default(false)->after('photo');
            $table->text('quote')->nullable()->after('is_leader');
            $table->string('linkedin_url')->nullable()->after('quote');
        });

        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('leadership_bg')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn('leadership_bg');
        });

        Schema::table('team_members', function (Blueprint $table) {
            $table->dropColumn(['is_leader', 'quote', 'linkedin_url']);
        });
    }
};
