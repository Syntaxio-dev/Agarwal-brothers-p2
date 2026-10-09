<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('heading')->nullable()->after('slug');
            $table->longText('content')->nullable()->after('description');
            $table->json('faqs')->nullable()->after('content');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('heading')->nullable()->after('slug');
            $table->string('model_group')->nullable()->after('heading');
            $table->longText('overview')->nullable()->after('short_description');
            $table->json('features')->nullable()->after('overview');
            $table->json('advantages')->nullable()->after('features');
            $table->json('gallery')->nullable()->after('image');
            $table->string('video_url')->nullable()->after('gallery');
            $table->json('documents')->nullable()->after('video_url');
            $table->json('faqs')->nullable()->after('documents');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['heading', 'model_group', 'overview', 'features', 'advantages', 'gallery', 'video_url', 'documents', 'faqs']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['heading', 'content', 'faqs']);
        });
    }
};
