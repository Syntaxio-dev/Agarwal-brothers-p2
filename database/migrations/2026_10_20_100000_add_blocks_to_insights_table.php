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
            // Article parts in order: text, single image, gallery/carousel, sources.
            $table->json('blocks')->nullable()->after('content');
        });

        // Existing articles keep their text: it becomes the first (and only) text part.
        DB::table('insights')->whereNotNull('content')->where('content', '!=', '')->orderBy('id')->each(function ($row) {
            DB::table('insights')->where('id', $row->id)->update([
                'blocks' => json_encode([['type' => 'text', 'data' => ['content' => $row->content]]]),
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('insights', function (Blueprint $table) {
            $table->dropColumn('blocks');
        });
    }
};
