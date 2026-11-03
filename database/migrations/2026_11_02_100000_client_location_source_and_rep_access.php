<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sales app v1.10.
 *
 *  - sales_clients.location_source: who gave the exact spot ('rep' = the rep who added the client, from Google Maps; 'office' = a manager on
 *    this website). A spot the office set cannot be moved by a phone.
 *  - sales_client_access: the manager lets a rep see a client (and the people at it) that is not the rep's own, for example "IIT Jodhpur"
 *    with all its teachers. A rep otherwise sees only the clients they added or that are given to them, and the people at those.
 * Additive only.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_clients', function (Blueprint $table) {
            $table->string('location_source', 10)->nullable()->after('location_at');
        });

        Schema::create('sales_client_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('sales_clients')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['client_id', 'user_id']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_client_access');

        Schema::table('sales_clients', function (Blueprint $table) {
            $table->dropColumn('location_source');
        });
    }
};
