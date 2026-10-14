<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** The 7 placeholder categories made by the first client-categories migration. */
    private array $starters = [
        'Pharma & Biotech', 'Dairy & Food', 'Government & Research Labs', 'Universities & Institutes',
        'Hospitals & Diagnostics', 'Agriculture & Environment', 'Chemicals & Industry',
    ];

    public function up(): void
    {
        // remove the 7 placeholders (their clients are left without a category: the owner picks the new one)
        $ids = DB::table('client_categories')->whereIn('name', $this->starters)->pluck('id');
        if ($ids->isNotEmpty()) {
            DB::table('clients')->whereIn('client_category_id', $ids)->update(['client_category_id' => null]);
            DB::table('client_categories')->whereIn('id', $ids)->delete();
        }

        // load the owner's list (Layer 2 of the industry categorisation), keeping any that already exist
        $existing = DB::table('client_categories')->pluck('name')->all();
        $order = (int) DB::table('client_categories')->max('sort_order');
        $rows = [];
        foreach (require database_path('data/client_categories.php') as $name) {
            if (in_array($name, $existing, true)) {
                continue;
            }
            $rows[] = ['name' => $name, 'sort_order' => ++$order, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()];
        }
        if ($rows) {
            DB::table('client_categories')->insert($rows);
        }
    }

    public function down(): void
    {
        // data load only: nothing to undo safely (clients may already use these categories)
    }
};
