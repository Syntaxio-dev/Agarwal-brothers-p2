<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Indexes for the columns the admin lists filter/sort on and the dashboard counts use. Performance only.
 *
 * Not helped by an index (so their counts are cached instead, see App\Support\DashboardCache):
 * Product::scopeWithoutSpecs (JSON_LENGTH(specs) = 0) and Product::scopeWithoutSeo (NULL OR '' on two columns)
 * have to look at every row.
 *
 * Every index is added only if the column exists and no index on exactly those columns is there yet
 * (foreign keys such as products.category_id, enquiries.assigned_to and brands.country_id already have one).
 * No composite index starts with a foreign-key column on purpose: MySQL then lets the foreign key use the new
 * index, drops its own, and refuses to drop ours again in down().
 */
return new class extends Migration
{
    /** table => list of column lists (one index each). */
    private const INDEXES = [
        'products' => [['is_active'], ['is_top_pick'], ['category_id'], ['created_at']],
        'enquiries' => [['status', 'created_at'], ['created_at']],
        'brands' => [['is_active'], ['country_id']],
        'contact_messages' => [['status', 'created_at'], ['created_at']],
        'job_applications' => [['status', 'created_at'], ['created_at']],
        'insights' => [['type', 'is_active'], ['is_active'], ['is_featured']],
    ];

    public function up(): void
    {
        foreach (self::INDEXES as $table => $sets) {
            foreach ($sets as $columns) {
                if (! Schema::hasTable($table) || ! Schema::hasColumns($table, $columns)) {
                    continue;
                }
                if ($this->covered($table, $columns)) {
                    continue;
                }

                Schema::table($table, fn (Blueprint $t) => $t->index($columns, $this->name($table, $columns)));
            }
        }
    }

    public function down(): void
    {
        foreach (self::INDEXES as $table => $sets) {
            foreach ($sets as $columns) {
                $name = $this->name($table, $columns);

                if (Schema::hasTable($table) && Schema::hasIndex($table, $name)) {
                    Schema::table($table, fn (Blueprint $t) => $t->dropIndex($name));
                }
            }
        }
    }

    /** True when some index (ours or an existing one, e.g. a foreign key) already starts with exactly these columns. */
    private function covered(string $table, array $columns): bool
    {
        foreach (Schema::getIndexes($table) as $index) {
            if (array_slice($index['columns'], 0, count($columns)) === $columns) {
                return true;
            }
        }

        return false;
    }

    private function name(string $table, array $columns): string
    {
        return $table . '_' . implode('_', $columns) . '_perf_index';
    }
};
