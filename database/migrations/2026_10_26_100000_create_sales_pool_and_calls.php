<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Sales module, step 2: ONE client pool for the whole team, the people at each client, and the call log.
 *  - sales_clients stops being private to a rep: every rep sees and can improve every client. `user_id` stays as "who added it",
 *    `assigned_to` says which rep works it (a manager sets it in the admin; a rep who adds a client gets it himself).
 *  - sales_contacts: the people at a client (name, position, phone). A call or a meeting is matched to a person.
 *  - sales_positions: the shared list of positions (job titles) behind the dropdown; reps can add to it.
 *  - sales_calls: only the calls the phone matched to a number of the pool (personal calls never reach the server).
 *  - sales_meetings gets the people who were met.
 * Additive only: nothing existing is dropped or renamed.
 */
return new class extends Migration
{
    private const PRESET_POSITIONS = [
        'Owner / Director', 'Purchase Manager', 'Procurement Head', 'Lab Head', 'Quality (QC) Head',
        'Production Head', 'Store Manager', 'Accounts', 'Other',
    ];

    public function up(): void
    {
        // (guarded: MySQL cannot undo a half-finished schema change, so a second run after a failure must not stop at "duplicate column")
        if (! Schema::hasColumn('sales_clients', 'assigned_to')) {
            Schema::table('sales_clients', function (Blueprint $table) {
                $table->foreignId('assigned_to')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->after('assigned_to')->constrained('users')->nullOnDelete();
                $table->index('city');
                $table->index('updated_at'); // every phone pulls the whole pool by this column
            });
        }

        // What each rep had added so far is also what he works: keep it theirs. The rows are touched (updated_at = now) on purpose:
        // phones of version 1.3 already hold a sync cursor, and a row older than that cursor would never reach the OTHER reps.
        DB::table('sales_clients')->update(['assigned_to' => DB::raw('user_id'), 'updated_at' => now()]);

        if (! Schema::hasColumn('sales_meetings', 'contact_uuids')) {
            Schema::table('sales_meetings', function (Blueprint $table) {
                $table->json('contact_uuids')->nullable()->after('client_name');   // the people met (uuids of sales_contacts)
            });
        }

        if (! Schema::hasTable('sales_contacts')) Schema::create('sales_contacts', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->char('client_uuid', 36)->index();                          // not a foreign key: offline, the client may arrive in a later push
            $table->foreignId('user_id')->constrained()->restrictOnDelete();    // who added the person
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name', 150);
            $table->string('position', 100)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('phone_normalized', 20)->nullable()->index();       // what a call is matched against
            $table->string('email', 150)->nullable();
            $table->string('notes', 1000)->nullable();
            $table->dateTime('client_updated_at', 3);
            $table->timestamps();
            $table->softDeletes();

            $table->index('updated_at');
        });

        if (! Schema::hasTable('sales_positions')) Schema::create('sales_positions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60)->unique();
            $table->boolean('is_preset')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        foreach (self::PRESET_POSITIONS as $name) {
            DB::table('sales_positions')->insertOrIgnore(['name' => $name, 'is_preset' => true, 'created_at' => now(), 'updated_at' => now()]);
        }

        if (! Schema::hasTable('sales_calls')) Schema::create('sales_calls', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();                                 // made from rep + number + time + type: importing twice makes one row
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('direction', 10);                                    // incoming, outgoing, missed
            $table->string('number', 20);                                       // E.164, always one of the pool's numbers
            $table->char('client_uuid', 36)->nullable()->index();
            $table->char('contact_uuid', 36)->nullable()->index();
            $table->string('client_name', 150)->nullable();                     // copies, so the log stays readable if the client is deleted
            $table->string('contact_name', 150)->nullable();
            $table->dateTime('started_at');
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->dateTime('client_updated_at', 3);
            $table->timestamps();

            $table->index(['user_id', 'started_at']);
            $table->index(['started_at']);
            $table->index('updated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_calls');
        Schema::dropIfExists('sales_positions');
        Schema::dropIfExists('sales_contacts');

        Schema::table('sales_meetings', function (Blueprint $table) {
            $table->dropColumn('contact_uuids');
        });

        Schema::table('sales_clients', function (Blueprint $table) {
            $table->dropIndex(['city']);
            $table->dropConstrainedForeignId('updated_by');
            $table->dropConstrainedForeignId('assigned_to');
        });
    }
};
