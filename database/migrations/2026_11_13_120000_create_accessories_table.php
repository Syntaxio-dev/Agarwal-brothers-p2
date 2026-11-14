<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('accessories')) {
            return;
        }

        Schema::create('accessories', function (Blueprint $table) {
            $table->id();
            // null = a general accessory that is offered with every brand
            $table->foreignId('brand_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('short_description', 300)->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'brand_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accessories');
    }
};
