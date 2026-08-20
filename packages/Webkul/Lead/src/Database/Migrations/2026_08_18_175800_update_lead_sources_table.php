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
        Schema::table('lead_sources', function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('default_lead_pipeline_id')->nullable();
            $table->unsignedInteger('default_lead_pipeline_stage_id')->nullable();
            $table->unsignedInteger('default_user_id')->nullable();

            $table->foreign('default_lead_pipeline_id')->references('id')->on('lead_pipelines')->onDelete('set null');
            $table->foreign('default_lead_pipeline_stage_id')->references('id')->on('lead_pipeline_stages')->onDelete('set null');
            $table->foreign('default_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lead_sources', function (Blueprint $table) {
            $table->dropForeign(['default_lead_pipeline_id']);
            $table->dropForeign(['default_lead_pipeline_stage_id']);
            $table->dropForeign(['default_user_id']);
            $table->dropColumn(['is_active', 'default_lead_pipeline_id', 'default_lead_pipeline_stage_id', 'default_user_id']);
        });
    }
};
