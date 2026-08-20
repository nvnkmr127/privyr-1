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
        Schema::table('activities', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('is_done');
            $table->string('priority')->nullable()->after('status');
            $table->datetime('completed_at')->nullable()->after('priority');
            $table->unsignedInteger('completed_by_id')->nullable()->after('completed_at');

            $table->foreign('completed_by_id')->references('id')->on('users')->onDelete('set null');

            $table->index('schedule_from');
            $table->index('status');
        });

        DB::statement("UPDATE activities SET status = 'completed' WHERE is_done = 1");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropForeign(['completed_by_id']);
            $table->dropIndex(['schedule_from']);
            $table->dropIndex(['status']);
            $table->dropColumn(['status', 'priority', 'completed_at', 'completed_by_id']);
        });
    }
};
