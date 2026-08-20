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
        Schema::create('lead_stage_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('lead_id')->unsigned();
            $table->integer('pipeline_id')->unsigned()->nullable();
            $table->integer('previous_stage_id')->unsigned()->nullable();
            $table->integer('new_stage_id')->unsigned();
            $table->integer('changed_by_id')->unsigned()->nullable();
            $table->timestamps();

            $table->foreign('lead_id')->references('id')->on('leads')->onDelete('cascade');
            $table->foreign('pipeline_id')->references('id')->on('lead_pipelines')->onDelete('cascade');
            $table->foreign('previous_stage_id')->references('id')->on('lead_pipeline_stages')->onDelete('set null');
            $table->foreign('new_stage_id')->references('id')->on('lead_pipeline_stages')->onDelete('cascade');
            $table->foreign('changed_by_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_stage_histories');
    }
};
