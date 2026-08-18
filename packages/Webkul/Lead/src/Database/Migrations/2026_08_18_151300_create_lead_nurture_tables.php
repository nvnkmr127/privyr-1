<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('lead_nurture_sequences', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('stop_condition')->nullable();
            $table->timestamps();
        });

        Schema::create('lead_nurture_steps', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('sequence_id');
            $table->string('type'); // message, wait, condition
            $table->json('config')->nullable(); // holds wait time, message content, or conditions
            $table->unsignedInteger('next_step_id')->nullable();
            $table->unsignedInteger('alt_next_step_id')->nullable();
            $table->timestamps();

            $table->foreign('sequence_id')->references('id')->on('lead_nurture_sequences')->onDelete('cascade');
        });

        Schema::create('lead_nurture_enrollments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('lead_id');
            $table->unsignedInteger('sequence_id');
            $table->unsignedInteger('current_step_id')->nullable();
            $table->string('status')->default('active'); // active, paused, completed, stopped, failed
            $table->timestamp('resume_at')->nullable();
            $table->json('context')->nullable();
            $table->timestamps();

            $table->foreign('lead_id')->references('id')->on('leads')->onDelete('cascade');
            $table->foreign('sequence_id')->references('id')->on('lead_nurture_sequences')->onDelete('cascade');
            $table->foreign('current_step_id')->references('id')->on('lead_nurture_steps')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('lead_nurture_enrollments');
        Schema::dropIfExists('lead_nurture_steps');
        Schema::dropIfExists('lead_nurture_sequences');
    }
};
