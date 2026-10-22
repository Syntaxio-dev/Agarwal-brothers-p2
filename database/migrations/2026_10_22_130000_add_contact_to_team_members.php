<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Optional contact details, shown on the Our Story team card when a visitor hovers it.
        Schema::table('team_members', function (Blueprint $table) {
            $table->string('phone', 30)->nullable()->after('designation');
            $table->string('email')->nullable()->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('team_members', fn (Blueprint $table) => $table->dropColumn(['phone', 'email']));
    }
};
