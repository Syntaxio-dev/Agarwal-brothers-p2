<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('industry', 40)->nullable()->after('name');
            $table->string('city', 80)->nullable()->after('industry');
            $table->unsignedSmallInteger('since_year')->nullable()->after('city');
            $table->string('supplied', 160)->nullable()->after('since_year');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['industry', 'city', 'since_year', 'supplied']);
        });
    }
};
