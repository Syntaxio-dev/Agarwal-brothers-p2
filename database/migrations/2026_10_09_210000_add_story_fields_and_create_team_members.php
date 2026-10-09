<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('story_banner')->nullable();
            $table->string('story_image_1')->nullable();
            $table->string('story_image_2')->nullable();
            $table->string('story_mission_image')->nullable();
            $table->text('story_intro')->nullable();
            $table->json('story_points')->nullable();
            $table->text('story_mission')->nullable();
            $table->text('story_vision')->nullable();
            $table->text('story_goal')->nullable();
            $table->unsignedSmallInteger('founded_year')->default(1981);
        });

        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('designation')->nullable();
            $table->string('photo')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_members');

        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'story_banner', 'story_image_1', 'story_image_2', 'story_mission_image',
                'story_intro', 'story_points', 'story_mission', 'story_vision', 'story_goal', 'founded_year',
            ]);
        });
    }
};
