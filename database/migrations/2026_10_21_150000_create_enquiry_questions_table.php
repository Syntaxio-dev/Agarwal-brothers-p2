<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Questions the visitor can answer on the product enquiry form, per vertical (no vertical = every product).
        Schema::create('enquiry_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vertical_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('question');
            $table->string('type', 20)->default('choice');   // choice = pick one, text = short answer
            $table->json('options')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // The answers are saved with the enquiry as a snapshot (question text + answer), so later edits to a question never change old enquiries.
        Schema::table('enquiries', function (Blueprint $table) {
            $table->json('answers')->nullable()->after('message');
        });
    }

    public function down(): void
    {
        Schema::table('enquiries', fn (Blueprint $table) => $table->dropColumn('answers'));
        Schema::dropIfExists('enquiry_questions');
    }
};
