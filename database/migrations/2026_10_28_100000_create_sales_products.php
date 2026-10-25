<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sales module, step 4: the PRODUCT GUIDE. One list for the whole team that a rep opens before a visit: what a product is, which brands
 * sit in the premium / economy / lite range, what to ask the client, what goes wrong in the first year and after it, and the top brands.
 *  - sales_products: written and edited by a manager only (the admin); every rep only reads it on the phone.
 *  - sales_product_requests: a rep asks for a note to be added or corrected; the manager answers in the admin.
 * Additive only: nothing existing is touched.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sales_products')) Schema::create('sales_products', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->string('name', 200);
            $table->string('slug', 220)->unique();
            $table->string('group', 100)->nullable()->index();          // a family of products (Centrifuges ...), for filtering on the phone
            $table->unsignedSmallInteger('sort')->default(0)->index();   // the order of the sheet the list came from
            $table->json('premium_brands')->nullable();                  // brands to suggest to a client with a big budget
            $table->json('economy_brands')->nullable();                  // ... a middle budget
            $table->json('lite_brands')->nullable();                     // ... a small budget (local brands)
            $table->text('summary')->nullable();                         // what the product is and where it is used
            $table->json('variants')->nullable();                        // [{ name, text }]
            $table->text('industries')->nullable();
            $table->json('key_parameters')->nullable();                  // questions to ask the client
            $table->json('short_term_problems')->nullable();             // list of text, first 12 months
            $table->json('long_term_problems')->nullable();              // list of text, after a year
            $table->json('top_brands')->nullable();                      // [{ name, note }]
            $table->boolean('is_active')->default(true);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        if (! Schema::hasTable('sales_product_requests')) Schema::create('sales_product_requests', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();                          // made on the phone: sending twice makes one row
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->char('product_uuid', 36)->nullable()->index();       // not a foreign key: the manager may have deleted the product
            $table->string('product_name', 200);                         // a copy, so the request stays readable
            $table->string('body', 500);
            $table->string('status', 12)->default('open')->index();      // open, done, declined
            $table->string('reply', 500)->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('handled_at')->nullable();
            $table->dateTime('client_updated_at', 3);
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_product_requests');
        Schema::dropIfExists('sales_products');
    }
};
