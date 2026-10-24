<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sales module (the field team and its Android app): every table is new and prefixed sales_. Nothing existing is altered.
 * Removing the module later = delete app/Sales and drop these tables; moving it to a sub-domain = take the same tables along.
 * Times are stored in UTC; the business timezone (Asia/Kolkata) is applied when rules are checked and when screens show them.
 * Records carry the UUID the phone made, so a sync can never create the same record twice.
 */
return new class extends Migration
{
    public function up(): void
    {
        // One row per field rep. The login itself is a normal `users` row with role field_rep (a role that cannot open /admin).
        Schema::create('sales_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('employee_code', 30)->unique();
            $table->string('phone', 20)->nullable()->unique();      // normalised, e.g. +919876543210
            $table->boolean('is_active')->default(true)->index();
            $table->dateTime('last_sync_at')->nullable();
            $table->string('app_version', 30)->nullable();
            $table->string('device_model', 100)->nullable();
            $table->timestamps();
        });

        Schema::create('sales_clients', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('name', 150);
            $table->string('company', 150)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('phone_normalized', 20)->nullable()->index();
            $table->string('city', 100)->nullable();
            $table->text('notes')->nullable();
            $table->dateTime('client_updated_at', 3);                 // the phone's own "last changed" time (conflict rule)
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'updated_at']);
        });

        Schema::create('sales_expenses', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('category', 20);                        // travel, food, lodging, fuel, other
            $table->unsignedBigInteger('amount_paise');            // whole paise, never a float
            $table->date('expense_date');
            $table->string('note', 1000)->nullable();
            $table->string('bill_path')->nullable();               // private disk, never public
            $table->string('bill_mime', 20)->nullable();
            $table->string('status', 20)->default('pending');      // pending, approved, rejected: only a manager changes it
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('reviewed_at')->nullable();
            $table->string('reject_reason', 500)->nullable();
            $table->dateTime('client_updated_at', 3);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'expense_date']);
            $table->index(['status', 'expense_date']);
            $table->index(['user_id', 'updated_at']);
        });

        Schema::create('sales_meetings', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->char('client_uuid', 36)->nullable()->index();  // not a foreign key: offline, the client may arrive in a later push
            $table->string('client_name', 150);                    // a copy, so the meeting stays readable if the client is deleted
            $table->dateTime('meeting_at');
            $table->string('purpose', 200)->nullable();
            $table->text('discussion')->nullable();
            $table->string('outcome', 200)->nullable();
            $table->dateTime('next_followup_at')->nullable();
            $table->dateTime('client_updated_at', 3);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'meeting_at']);
            $table->index(['user_id', 'updated_at']);
            $table->index('next_followup_at');
        });

        // One row per push: what came in and what happened to it (support and failure tracing).
        Schema::create('sales_sync_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('received')->default(0);
            $table->unsignedInteger('accepted')->default(0);
            $table->unsignedInteger('duplicates')->default(0);
            $table->unsignedInteger('rejected')->default(0);
            $table->json('errors')->nullable();
            $table->string('app_version', 30)->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        foreach (['sales_sync_batches', 'sales_meetings', 'sales_expenses', 'sales_clients', 'sales_profiles'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
