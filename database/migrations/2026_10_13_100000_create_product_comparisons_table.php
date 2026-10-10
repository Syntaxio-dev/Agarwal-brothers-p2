<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** How often two products were compared by different visitors (counts only, no personal data). */
    public function up(): void
    {
        Schema::create('product_comparisons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('other_product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedInteger('times')->default(1);
            $table->timestamps();

            $table->unique(['product_id', 'other_product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_comparisons');
    }
};
