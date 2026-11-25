<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sales module, app v1.20: TASKS from the manager, and the rep's own TO-DO list and NOTES. Additive only.
 *  - sales_tasks: a manager gives a rep work ("send a quotation to this e-mail, here are the specs"), with a due date, a priority, an e-mail to
 *    send to and up to 5 files. The rep moves it on from the phone (status + a note); everything else belongs to the office. `version` counts the
 *    office's own changes, so a phone never has to compare clocks.
 *  - sales_task_files: the files of a task (pdf, picture, Excel, Word ...), on the PRIVATE disk.
 *  - sales_todos / sales_memos: the rep's private list and notes, kept here only so a new phone gets them back (no admin screen reads them).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sales_tasks')) Schema::create('sales_tasks', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignId('assigned_to')->constrained('users')->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->string('send_to_email', 150)->nullable();          // "send the quotation to this address"
            $table->dateTime('due_at')->nullable();
            $table->string('priority', 8)->default('normal');         // normal, high
            $table->string('status', 12)->default('new')->index();    // new, in_progress, done, blocked, cancelled
            $table->string('rep_note', 500)->nullable();               // what the rep wrote back
            $table->dateTime('rep_updated_at', 3)->nullable();         // when the rep last moved it (their own clock, the newest wins)
            $table->unsignedInteger('version')->default(1);            // +1 on every change made here
            $table->timestamps();
            $table->softDeletes();

            $table->index(['assigned_to', 'status']);
        });

        if (! Schema::hasTable('sales_task_files')) Schema::create('sales_task_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('sales_tasks')->cascadeOnDelete();
            $table->string('name', 120);                               // the name the manager's file had
            $table->string('mime', 100);
            $table->unsignedInteger('size');
            $table->string('path', 255);                               // on the private disk, a random name
            $table->timestamps();
        });

        if (! Schema::hasTable('sales_todos')) Schema::create('sales_todos', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('title', 200);
            $table->boolean('done')->default(false);
            $table->dateTime('done_at', 3)->nullable();
            $table->date('due_date')->nullable();
            $table->dateTime('client_updated_at', 3);
            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id');
        });

        if (! Schema::hasTable('sales_memos')) Schema::create('sales_memos', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('title', 120)->nullable();
            $table->text('body');
            $table->dateTime('client_updated_at', 3);
            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_memos');
        Schema::dropIfExists('sales_todos');
        Schema::dropIfExists('sales_task_files');
        Schema::dropIfExists('sales_tasks');
    }
};
