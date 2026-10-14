<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** The first starter list (it replaces the old fixed "industry" list in config); the owner edits it in the admin. */
    private array $starter = [
        'pharma' => 'Pharma & Biotech',
        'dairy' => 'Dairy & Food',
        'government' => 'Government & Research Labs',
        'academia' => 'Universities & Institutes',
        'healthcare' => 'Hospitals & Diagnostics',
        'agriculture' => 'Agriculture & Environment',
        'industry' => 'Chemicals & Industry',
    ];

    public function up(): void
    {
        Schema::create('client_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->foreignId('client_category_id')->nullable()->after('name')->constrained('client_categories')->nullOnDelete();
        });

        // carry the old industry of every client over, then drop the old column
        $order = 0;
        foreach ($this->starter as $key => $name) {
            $id = DB::table('client_categories')->insertGetId([
                'name' => $name, 'sort_order' => ++$order, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('clients')->where('industry', $key)->update(['client_category_id' => $id]);
        }

        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('industry');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('industry', 40)->nullable()->after('name');
        });

        $keys = array_flip($this->starter);
        foreach (DB::table('client_categories')->get() as $category) {
            if (isset($keys[$category->name])) {
                DB::table('clients')->where('client_category_id', $category->id)->update(['industry' => $keys[$category->name]]);
            }
        }

        Schema::table('clients', function (Blueprint $table) {
            $table->dropConstrainedForeignId('client_category_id');
        });

        Schema::dropIfExists('client_categories');
    }
};
