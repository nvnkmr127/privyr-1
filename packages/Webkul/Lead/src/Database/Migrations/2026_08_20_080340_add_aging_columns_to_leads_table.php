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
            $table->timestamp('last_activity_at')->nullable()->after('next_follow_up_at');
            $table->timestamp('stage_changed_at')->nullable()->after('last_activity_at');

            // Add indexes for efficient querying
            $table->index('stage_changed_at');
            $table->index('last_activity_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex(['stage_changed_at']);
            $table->dropIndex(['last_activity_at']);

            $table->dropColumn(['last_activity_at', 'stage_changed_at']);
        });
    }
};
