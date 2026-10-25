<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sales module, step 3: enquiries (what a client asked a rep for, with follow-ups) and the reps' daily reports.
 *  - sales_enquiries: one row per enquiry. An enquiry can ask for several products at once (`items`, a small json list: product,
 *    preferred brands, quantity, budget), has a status (new, price to send, price sent, call back, meeting planned, negotiation,
 *    won, lost), the next follow-up moment, and a `timeline` of notes and status changes. Private to the rep who made it; managers see all.
 *  - sales_daily_reports: one row per rep per day (what the rep wrote, plus the figures the phone showed when it was sent).
 * Additive only.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_enquiries', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->char('client_uuid', 36)->nullable()->index();        // not a foreign key: offline, the client may arrive in a later push
            $table->string('client_name', 150);                          // a copy, so the enquiry stays readable if the client is deleted
            $table->char('contact_uuid', 36)->nullable();
            $table->string('contact_name', 150)->nullable();
            $table->string('status', 20)->default('new');
            $table->json('items');                                       // [{product, brands, quantity, budget_paise}]
            $table->dateTime('opened_at');                               // when the rep wrote it down
            $table->dateTime('next_followup_at')->nullable();
            $table->dateTime('closed_at')->nullable();
            $table->json('timeline')->nullable();                        // [{at, text, status}]
            $table->dateTime('client_updated_at', 3);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'updated_at']);
            $table->index('next_followup_at');
            $table->index('status');
        });

        Schema::create('sales_daily_reports', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->date('report_date');
            $table->text('notes')->nullable();
            $table->text('tomorrow_plan')->nullable();
            $table->text('issues')->nullable();                          // anything the rep needs help with
            $table->json('figures')->nullable();                         // what the phone showed: calls, meetings, enquiries, expenses
            $table->dateTime('submitted_at')->nullable();
            $table->dateTime('client_updated_at', 3);
            $table->timestamps();

            $table->unique(['user_id', 'report_date']);
            $table->index('report_date');
            $table->index(['user_id', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_daily_reports');
        Schema::dropIfExists('sales_enquiries');
    }
};
