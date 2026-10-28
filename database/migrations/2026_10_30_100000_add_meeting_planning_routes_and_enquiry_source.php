<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sales app v1.7: meetings that can be planned, routes, and "came through" on an enquiry.
 *
 *  - sales_meetings: `status` (planned | done | missed | moved; every row that exists today is a meeting written after it happened, so
 *    the default is "done"), `scheduled_at` (the plan; meeting_at stays the one time the office always sorts by), `ended_at`,
 *    `route_uuid`, `result`, `moved_from`, `time_manual`.
 *  - sales_routes: several meetings of one day as one trip (a rep's own; managers read all).
 *  - sales_enquiries: `source` (call | walkin | whatsapp | reference), optional.
 * Additive only: nothing is renamed or dropped, and old phones keep working.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_meetings', function (Blueprint $table) {
            $table->string('status', 10)->default('done')->after('outcome');
            $table->dateTime('scheduled_at')->nullable()->after('status');
            $table->dateTime('ended_at')->nullable()->after('scheduled_at');
            $table->char('route_uuid', 36)->nullable()->after('ended_at');          // not a foreign key: offline, the route may arrive in a later push
            $table->string('result', 20)->nullable()->after('route_uuid');
            $table->dateTime('moved_from')->nullable()->after('result');
            $table->boolean('time_manual')->default(false)->after('moved_from');    // the rep picked the time by hand: a route's spacing never changes it

            $table->index('status');
            $table->index('route_uuid');
            $table->index(['user_id', 'scheduled_at']);
        });

        Schema::create('sales_routes', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->date('route_date');                                              // the one day of all its stops (business timezone)
            $table->string('title', 60);
            $table->string('area', 100)->nullable();                                 // the place, for example Bhiwadi
            $table->string('start_time', 5)->default('09:00');                       // "HH:MM"
            $table->boolean('overnight')->default(false);
            $table->json('stops_order');                                             // [meeting uuid] in the order of the trip
            $table->dateTime('client_updated_at', 3);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'route_date']);
            $table->index(['user_id', 'updated_at']);
            $table->index('route_date');
        });

        Schema::table('sales_enquiries', function (Blueprint $table) {
            $table->string('source', 12)->nullable()->after('status');               // call | walkin | whatsapp | reference
            $table->index('source');
        });
    }

    public function down(): void
    {
        Schema::table('sales_enquiries', function (Blueprint $table) {
            $table->dropIndex(['source']);
            $table->dropColumn('source');
        });

        Schema::dropIfExists('sales_routes');

        Schema::table('sales_meetings', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'scheduled_at']);
            $table->dropIndex(['route_uuid']);
            $table->dropIndex(['status']);
            $table->dropColumn(['status', 'scheduled_at', 'ended_at', 'route_uuid', 'result', 'moved_from', 'time_manual']);
        });
    }
};
