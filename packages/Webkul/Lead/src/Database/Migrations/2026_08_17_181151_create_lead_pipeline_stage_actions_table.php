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
        Schema::create('lead_pipeline_stage_actions', function (Blueprint $table) {
            $table->id();
            $table->integer('lead_pipeline_stage_id')->unsigned();
            $table->string('type');
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->foreign('lead_pipeline_stage_id')
                ->references('id')
                ->on('lead_pipeline_stages')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_pipeline_stage_actions');
    }
};
