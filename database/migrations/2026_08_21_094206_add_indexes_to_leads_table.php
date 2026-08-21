<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('status');
            $table->index('lead_pipeline_stage_id');
            $table->index('next_follow_up_at');
            $table->index('is_archived');
            $table->index(['lead_source_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['lead_pipeline_stage_id']);
            $table->dropIndex(['next_follow_up_at']);
            $table->dropIndex(['is_archived']);
            $table->dropIndex(['lead_source_id', 'status']);
        });
    }
};
