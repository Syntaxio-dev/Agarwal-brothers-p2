<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('insights', function (Blueprint $table) {
            $table->foreignId('brand_id')->nullable()->after('type')->constrained()->nullOnDelete();
            $table->dateTime('starts_at')->nullable()->after('event_date');
            $table->boolean('time_tbd')->default(false)->after('starts_at');
            $table->string('venue')->nullable()->after('time_tbd');
            $table->string('registration_url')->nullable()->after('venue');
            $table->string('recording_url')->nullable()->after('registration_url');
        });

        // Existing webinars only had a date; keep them working as "time to be announced".
        DB::table('insights')
            ->where('type', 'webinar')
            ->whereNotNull('event_date')
            ->whereNull('starts_at')
            ->update([
                'starts_at' => DB::raw("CONCAT(event_date, ' 00:00:00')"),
                'time_tbd' => true,
            ]);
    }

    public function down(): void
    {
        Schema::table('insights', function (Blueprint $table) {
            $table->dropConstrainedForeignId('brand_id');
            $table->dropColumn(['starts_at', 'time_tbd', 'venue', 'registration_url', 'recording_url']);
        });
    }
};
