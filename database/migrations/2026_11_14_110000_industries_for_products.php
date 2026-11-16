<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // the industry list = the client categories; each one gets a web address and can be linked to products
        if (! Schema::hasColumn('client_categories', 'slug')) {
            Schema::table('client_categories', function (Blueprint $table) {
                $table->string('slug', 120)->nullable()->after('name');
            });
        }

        $used = [];
        foreach (DB::table('client_categories')->orderBy('id')->get() as $row) {
            $base = Str::slug($row->name) ?: 'industry';
            $slug = $base;
            $n = 2;
            while (isset($used[$slug])) {
                $slug = $base . '-' . $n++;
            }
            $used[$slug] = true;
            DB::table('client_categories')->where('id', $row->id)->update(['slug' => $slug]);
        }

        Schema::table('client_categories', function (Blueprint $table) {
            $table->unique('slug');
        });

        // the click-through vertical of the previous version is replaced by the product list
        if (Schema::hasColumn('client_categories', 'vertical_id')) {
            Schema::table('client_categories', function (Blueprint $table) {
                $table->dropConstrainedForeignId('vertical_id');
            });
        }

        Schema::create('client_category_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unique(['client_category_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_category_product');

        Schema::table('client_categories', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
