<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sales module, app v1.19: PROSPECTING and BUG REPORTS. Additive only: nothing existing is touched.
 *  - sales_prospects: companies that do NOT work with us yet, found online or on the road, for cold calling. One pool for the whole team
 *    (every rep reads it, any rep adds; only the rep who added a prospect changes its details, a manager can change anything).
 *  - sales_prospect_people: the people at a prospect (name, position, phone, e-mail, LinkedIn). Replaced as a whole on every save from a phone.
 *  - sales_prospect_notes: one call to a prospect, by any rep. A log: sent once, never changed. The newest one is the prospect's status.
 *  - sales_bug_reports: a rep's "report a bug" from the app. Sent once; a manager answers in the admin.
 *  - sales_meetings.prospect_uuid: a visit planned to a prospect ("Plan a route" mixes clients and prospects); a plain reference, not a foreign key.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sales_prospects')) Schema::create('sales_prospects', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();                              // made on the phone: sending twice makes one row
            $table->foreignId('user_id')->constrained()->restrictOnDelete(); // the rep who added it
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name', 150);
            $table->string('name_key', 160)->index();                        // lower case letters and digits only: "one company once per territory"
            $table->string('territory', 100);
            $table->string('territory_key', 110)->index();
            $table->string('address', 200)->nullable();
            $table->string('source', 20)->nullable();                        // google, linkedin, indiamart, directory, visit, reference, other
            $table->string('notes', 1000)->nullable();
            $table->dateTime('client_updated_at', 3);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['territory_key', 'name_key']);
        });

        if (! Schema::hasTable('sales_prospect_people')) Schema::create('sales_prospect_people', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prospect_id')->constrained('sales_prospects')->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('position', 100)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('phone_normalized', 20)->nullable()->index();     // one number once in the whole prospect pool
            $table->string('email', 150)->nullable();
            $table->string('linkedin', 200)->nullable();                     // only a linkedin.com address is ever stored
            $table->timestamps();
        });

        if (! Schema::hasTable('sales_prospect_notes')) Schema::create('sales_prospect_notes', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->char('prospect_uuid', 36)->index();                      // not a foreign key: a deleted prospect keeps its history for the office
            $table->foreignId('user_id')->constrained()->restrictOnDelete(); // the rep who called
            $table->string('outcome', 20)->index();                          // no_answer, call_back, interested, meeting_fixed, not_interested, wrong_number, do_not_call, converted
            $table->string('remark', 300)->nullable();
            $table->dateTime('next_followup_at')->nullable();
            $table->dateTime('noted_at', 3);                                 // when the rep made the call (the phone's own time)
            $table->dateTime('client_updated_at', 3);
            $table->timestamps();

            $table->index(['prospect_uuid', 'noted_at']);
        });

        if (! Schema::hasTable('sales_bug_reports')) Schema::create('sales_bug_reports', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('area', 20)->index();                             // where it happened: expenses, clients, prospects, leads, meetings, guide, calls, sync, other
            $table->string('body', 1000);
            $table->string('app_version', 30)->nullable();
            $table->string('status', 12)->default('open')->index();         // open, fixed, declined
            $table->string('reply', 500)->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('handled_at')->nullable();
            $table->dateTime('client_updated_at', 3);
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        if (Schema::hasTable('sales_meetings') && ! Schema::hasColumn('sales_meetings', 'prospect_uuid')) {
            Schema::table('sales_meetings', function (Blueprint $table) {
                $table->char('prospect_uuid', 36)->nullable()->after('client_uuid');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('sales_meetings', 'prospect_uuid')) {
            Schema::table('sales_meetings', fn (Blueprint $table) => $table->dropColumn('prospect_uuid'));
        }

        Schema::dropIfExists('sales_bug_reports');
        Schema::dropIfExists('sales_prospect_notes');
        Schema::dropIfExists('sales_prospect_people');
        Schema::dropIfExists('sales_prospects');
    }
};
