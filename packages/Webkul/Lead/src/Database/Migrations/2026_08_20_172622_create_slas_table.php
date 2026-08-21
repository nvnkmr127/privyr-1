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
        Schema::create('slas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            
            $table->integer('assignment_sla_duration')->nullable()->comment('Duration in minutes');
            $table->integer('first_action_sla_duration')->nullable()->comment('Duration in minutes');
            $table->integer('follow_up_sla_duration')->nullable()->comment('Duration in minutes');
            $table->integer('stage_sla_duration')->nullable()->comment('Duration in minutes');
            
            $table->unsignedInteger('source_id')->nullable();
            $table->unsignedInteger('pipeline_id')->nullable();
            $table->unsignedInteger('stage_id')->nullable();
            $table->unsignedInteger('type_id')->nullable();
            $table->unsignedInteger('team_id')->nullable();
            
            $table->integer('sort_order')->default(0);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slas');
    }
};
