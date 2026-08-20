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
            $table->integer('group_id')->unsigned()->nullable()->after('user_id');
            $table->foreign('group_id')->references('id')->on('groups')->onDelete('set null');
        });

        // Add previous_group_id and new_group_id to lead_assignments (history)
        Schema::table('lead_assignments', function (Blueprint $table) {
            $table->integer('previous_group_id')->unsigned()->nullable()->after('previous_owner');
            $table->foreign('previous_group_id')->references('id')->on('groups')->onDelete('set null');

            $table->integer('assigned_group_id')->unsigned()->nullable()->after('assigned_to');
            $table->foreign('assigned_group_id')->references('id')->on('groups')->onDelete('set null');
        });

        // Update lead_assignment_rules to support assigning to groups
        Schema::create('lead_assignment_rule_groups', function (Blueprint $table) {
            $table->unsignedBigInteger('rule_id');
            $table->unsignedInteger('group_id');

            $table->foreign('rule_id')->references('id')->on('lead_assignment_rules')->onDelete('cascade');
            $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
            $table->primary(['rule_id', 'group_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_assignment_rule_groups');

        Schema::table('lead_assignments', function (Blueprint $table) {
            $table->dropForeign(['previous_group_id']);
            $table->dropColumn('previous_group_id');
            $table->dropForeign(['assigned_group_id']);
            $table->dropColumn('assigned_group_id');
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
            $table->dropColumn('group_id');
        });
    }
};
