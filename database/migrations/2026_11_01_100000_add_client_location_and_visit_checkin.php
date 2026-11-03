<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sales app v1.8: proof of a visit.
 *
 *  - sales_clients: the saved spot of the client (`lat`, `lng`, accuracy, when). Every client a rep adds now has one; older rows have none.
 *  - sales_meetings: the check-in a rep makes at the client with a selfie: the spot, the accuracy, the moment the phone says,
 *    `checkin_received_at` (the moment the office heard of it, so a wrong phone clock shows), `checkin_distance_m` (worked out HERE from the
 *    client's spot, never taken from the phone) and the selfie file on the private disk.
 * Additive only: nothing is renamed or dropped, and old phones keep working.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_clients', function (Blueprint $table) {
            $table->decimal('lat', 9, 6)->nullable()->after('notes');
            $table->decimal('lng', 9, 6)->nullable()->after('lat');
            $table->unsignedInteger('location_accuracy_m')->nullable()->after('lng');
            $table->dateTime('location_at')->nullable()->after('location_accuracy_m');
        });

        Schema::table('sales_meetings', function (Blueprint $table) {
            $table->dateTime('checkin_at')->nullable()->after('time_manual');
            $table->decimal('checkin_lat', 9, 6)->nullable()->after('checkin_at');
            $table->decimal('checkin_lng', 9, 6)->nullable()->after('checkin_lat');
            $table->unsignedInteger('checkin_accuracy_m')->nullable()->after('checkin_lng');
            $table->unsignedInteger('checkin_distance_m')->nullable()->after('checkin_accuracy_m');
            $table->dateTime('checkin_received_at')->nullable()->after('checkin_distance_m');
            $table->string('selfie_path')->nullable()->after('checkin_received_at');
            $table->string('selfie_mime', 40)->nullable()->after('selfie_path');

            $table->index('checkin_at');
        });
    }

    public function down(): void
    {
        Schema::table('sales_meetings', function (Blueprint $table) {
            $table->dropIndex(['checkin_at']);
            $table->dropColumn(['checkin_at', 'checkin_lat', 'checkin_lng', 'checkin_accuracy_m', 'checkin_distance_m', 'checkin_received_at', 'selfie_path', 'selfie_mime']);
        });

        Schema::table('sales_clients', function (Blueprint $table) {
            $table->dropColumn(['lat', 'lng', 'location_accuracy_m', 'location_at']);
        });
    }
};
