<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Snapshot fields for shareable /report/{id} (not re-running AI on view).
     */
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->json('scam_flags')->nullable()->after('scam_score');
            $table->text('ai_summary')->nullable()->after('scam_flags');
            $table->unsignedInteger('market_average')->nullable()->after('ai_summary');
            $table->integer('market_difference_percent')->nullable()->after('market_average');
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn([
                'scam_flags',
                'ai_summary',
                'market_average',
                'market_difference_percent',
            ]);
        });
    }
};
