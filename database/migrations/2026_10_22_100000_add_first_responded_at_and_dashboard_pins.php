<?php

use App\Models\Note;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // When the team first answered an enquiry (for the dashboard's response-time card).
        Schema::table('enquiries', function (Blueprint $table) {
            $table->timestamp('first_responded_at')->nullable()->after('assigned_to');
        });

        // Each admin's own pinned shortcuts on the dashboard.
        Schema::table('users', function (Blueprint $table) {
            $table->json('dashboard_pins')->nullable();
        });

        // Old enquiries: the first automatic "Status changed from New ..." note is the moment somebody first acted.
        // Where there is no such note the time stays empty (the dashboard then simply does not count that enquiry).
        $rows = DB::table('notes')
            ->where('noteable_type', 'App\\Models\\Enquiry')
            ->where('kind', Note::KIND_SYSTEM)
            ->where('body', 'like', 'Status changed from New%')
            ->selectRaw('noteable_id, MIN(created_at) as at')
            ->groupBy('noteable_id')
            ->get();

        foreach ($rows as $row) {
            DB::table('enquiries')->where('id', $row->noteable_id)->whereNull('first_responded_at')->update(['first_responded_at' => $row->at]);
        }
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('dashboard_pins'));
        Schema::table('enquiries', fn (Blueprint $table) => $table->dropColumn('first_responded_at'));
    }
};
