<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_top_pick')
                ->default(false)
                ->after('is_active');

            $table->unsignedInteger('top_pick_order')
                ->nullable()
                ->after('is_top_pick');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'is_top_pick',
                'top_pick_order',
            ]);
        });
    }
};