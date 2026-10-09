<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = ['verticals', 'categories', 'brands', 'products', 'insights'];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->string('seo_title')->nullable();
                $t->string('seo_description', 320)->nullable();
                $t->string('og_image')->nullable();
            });
        }

        Schema::table('site_settings', function (Blueprint $t) {
            $t->string('seo_title')->nullable();
            $t->string('seo_description', 320)->nullable();
            $t->string('default_og_image')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $t) {
            $t->dropColumn(['seo_title', 'seo_description', 'default_og_image']);
        });

        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropColumn(['seo_title', 'seo_description', 'og_image']);
            });
        }
    }
};
