<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('slides', function (Blueprint $table) {
            // the product a slide features; the slide links to its page. Clearing the product (or deleting it) just unlinks.
            $table->foreignId('product_id')->nullable()->after('link_url')->constrained('products')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('slides', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_id');
        });
    }
};
