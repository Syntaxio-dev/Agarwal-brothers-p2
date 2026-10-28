<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Indexes only (no column changes): the redesigned Sales management page asks about the whole team at once ("meetings of today",
 * "planned visits an hour late", "enquiries opened / decided in the last 90 days", "follow-ups overdue"), not about one rep. The tables
 * had indexes per rep (user_id first) but none that starts with the time or the status, so those questions scanned the table.
 */
return new class extends Migration
{
    public function up(): void
    {
        // MySQL cannot roll a half-done migration back: each index is added only when it is not there yet, so a second run finishes the job
        $this->add('sales_meetings', ['meeting_at']);                       // today's meetings, the windows of the six figures
        $this->add('sales_meetings', ['status', 'meeting_at']);             // done in a window; planned and not logged
        $this->add('sales_enquiries', ['opened_at']);                       // new enquiries, where they came from, what clients ask for
        $this->add('sales_enquiries', ['status', 'closed_at']);             // won / lost in a window (win rate)
        $this->add('sales_enquiries', ['status', 'next_followup_at']);      // open enquiries with a follow-up that is overdue or due today
    }

    private function add(string $table, array $columns): void
    {
        if (! Schema::hasIndex($table, $columns)) {
            Schema::table($table, fn (Blueprint $blueprint) => $blueprint->index($columns));
        }
    }

    public function down(): void
    {
        Schema::table('sales_enquiries', function (Blueprint $table) {
            $table->dropIndex(['status', 'next_followup_at']);
            $table->dropIndex(['status', 'closed_at']);
            $table->dropIndex(['opened_at']);
        });

        Schema::table('sales_meetings', function (Blueprint $table) {
            $table->dropIndex(['status', 'meeting_at']);
            $table->dropIndex(['meeting_at']);
        });
    }
};
