<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sales app v1.15: leads. A lead IS an enquiry; what the rep does on each follow-up is kept on it:
 *  - lead_status: where the follow-up stands (new, price_sent, better_option, call_not_picking, want_less_price, interested, waiting,
 *    not_interested, deal_done, deal_lost). Null = an enquiry from before v1.15, shown as "New lead".
 *  - lead_remark: what the client said last time (the newest remark; the history is in the timeline).
 *  - last_followup_at: when the rep last logged a follow-up.
 * The next reminder is the existing next_followup_at. Additive only: old phones keep working and never wipe these.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_enquiries', function (Blueprint $table) {
            $table->string('lead_status', 30)->nullable()->after('source');
            $table->string('lead_remark', 300)->nullable()->after('lead_status');
            $table->dateTime('last_followup_at', 3)->nullable()->after('lead_remark');

            $table->index('lead_status');
            $table->index(['user_id', 'next_followup_at']);
        });
    }

    public function down(): void
    {
        Schema::table('sales_enquiries', function (Blueprint $table) {
            $table->dropIndex(['lead_status']);
            $table->dropIndex(['user_id', 'next_followup_at']);
            $table->dropColumn(['lead_status', 'lead_remark', 'last_followup_at']);
        });
    }
};
