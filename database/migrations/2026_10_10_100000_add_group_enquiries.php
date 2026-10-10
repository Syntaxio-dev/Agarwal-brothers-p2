<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enquiries', function (Blueprint $table) {
            $table->string('company')->nullable()->after('phone');
        });

        // One row per product in a group ("enquiry list") enquiry. Names are copied so the
        // enquiry still reads correctly if the product is renamed or removed later.
        Schema::create('enquiry_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enquiry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');
            $table->string('brand_name')->nullable();
            $table->string('category_name')->nullable();
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enquiry_items');
        Schema::table('enquiries', function (Blueprint $table) {
            $table->dropColumn('company');
        });
    }
};
