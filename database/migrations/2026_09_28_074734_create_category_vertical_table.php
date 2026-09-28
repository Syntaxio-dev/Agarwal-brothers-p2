<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_vertical', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vertical_id')->constrained()->cascadeOnDelete();
            $table->unique(['category_id', 'vertical_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_vertical');
    }
};