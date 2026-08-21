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
        Schema::table('lead_assignment_rules', function (Blueprint $table) {
            $table->string('fallback_type')->default('none'); // none, unassigned, user, team
            $table->unsignedInteger('fallback_user_id')->nullable();
            $table->unsignedInteger('fallback_group_id')->nullable();

            $table->foreign('fallback_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('fallback_group_id')->references('id')->on('groups')->onDelete('set null');
        });

        Schema::table('lead_assignment_rule_users', function (Blueprint $table) {
            $table->integer('capacity')->nullable();
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->index(['user_id', 'group_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'group_id']);
        });

        Schema::table('lead_assignment_rule_users', function (Blueprint $table) {
            $table->dropColumn('capacity');
        });

        Schema::table('lead_assignment_rules', function (Blueprint $table) {
            $table->dropForeign(['fallback_user_id']);
            $table->dropForeign(['fallback_group_id']);
            $table->dropColumn(['fallback_type', 'fallback_user_id', 'fallback_group_id']);
        });
    }
};
