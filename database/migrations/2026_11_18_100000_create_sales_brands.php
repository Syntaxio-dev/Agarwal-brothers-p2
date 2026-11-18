<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The brand guide of the Sales app: what each brand we sell is best at, its well-known lines, where it fits, how to pitch it, how
 * to answer a client's doubt, honest limits and its competitors. Only a manager writes it (Sales team > Brand guide); the phones
 * read it together with the product guide. The lists are JSON so a brand is one row.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_brands', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name', 100)->unique();
            $table->string('origin', 160)->nullable();      // "Germany · Sartorius AG"
            $table->string('channel', 160)->nullable();     // who we buy it through, for example "Bishop"
            $table->text('about')->nullable();
            $table->json('known_for')->nullable();          // what it is best at
            $table->json('known_lines')->nullable();        // its best-known product lines
            $table->json('best_for')->nullable();           // where it fits best
            $table->json('pitch')->nullable();              // selling points
            $table->json('objections')->nullable();         // [{ if, then }]
            $table->json('watch_out')->nullable();          // honest limits
            $table->json('competitors')->nullable();        // [{ name, note }]
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_brands');
    }
};
