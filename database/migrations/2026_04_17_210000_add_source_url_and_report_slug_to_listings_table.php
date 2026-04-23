<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->string('source_url', 2048)->nullable()->after('raw_input');
            $table->string('report_slug', 191)->nullable()->unique()->after('market_difference_percent');
        });

        $rows = DB::table('listings')->orderBy('id')->get();
        foreach ($rows as $row) {
            $created = Carbon::parse($row->created_at);
            $slug = sprintf(
                'advertentie-%s-%s-%d',
                $created->format('Y-m-d'),
                $created->format('H-i-s'),
                $row->id
            );
            DB::table('listings')->where('id', $row->id)->update(['report_slug' => $slug]);
        }
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn(['source_url', 'report_slug']);
        });
    }
};
