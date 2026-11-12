<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Enquiries have six stages now (Qualification, Need Analysis, Proposal / Price Quote, Negotiation / Review, Closed Won, Closed Lost).
 * The eight old stage names become one of them (App\Sales\Models\SalesEnquiry::LEGACY), in the status column and in every timeline line.
 * Plain updates on purpose: this only renames, so the phones' "last changed" time is left alone (a phone reads old names the same way).
 * Not reversible: a stage that was merged cannot be told apart again.
 */
return new class extends Migration
{
    private const MAP = ['new' => 'qualification', 'callback' => 'qualification', 'meeting_planned' => 'need_analysis', 'price_to_send' => 'proposal', 'price_sent' => 'proposal'];

    public function up(): void
    {
        foreach (self::MAP as $old => $new) {
            DB::table('sales_enquiries')->where('status', $old)->update(['status' => $new]);
        }

        DB::table('sales_enquiries')->whereNotNull('timeline')->orderBy('id')->select(['id', 'timeline'])->chunkById(200, function ($rows) {
            foreach ($rows as $row) {
                $lines = json_decode((string) $row->timeline, true);
                if (! is_array($lines)) {
                    continue;
                }

                $changed = false;
                foreach ($lines as &$line) {
                    if (is_array($line) && isset($line['status'], self::MAP[$line['status']])) {
                        $line['status'] = self::MAP[$line['status']];
                        $changed = true;
                    }
                }
                unset($line);

                if ($changed) {
                    DB::table('sales_enquiries')->where('id', $row->id)->update(['timeline' => json_encode($lines, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
                }
            }
        });

        Schema::table('sales_enquiries', function (Blueprint $table) {
            $table->string('status', 20)->default('qualification')->change();
        });
    }

    public function down(): void
    {
        Schema::table('sales_enquiries', function (Blueprint $table) {
            $table->string('status', 20)->default('new')->change();
        });
    }
};
