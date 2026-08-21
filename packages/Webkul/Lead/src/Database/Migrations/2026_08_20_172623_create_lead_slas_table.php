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
        Schema::create('lead_slas', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('lead_id');
            $table->unsignedBigInteger('sla_id')->nullable();

            $table->string('sla_type'); // Assignment, First Action, Follow-up, Stage
            $table->string('status'); // On Track, Due Soon, Breached, Resolved, Not Applicable

            $table->timestamp('started_at')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamp('breached_at')->nullable();
            $table->timestamp('resolved_at')->nullable();

            $table->unsignedInteger('owner_id')->nullable();
            $table->unsignedInteger('team_id')->nullable();
            $table->unsignedInteger('stage_id')->nullable();

            $table->timestamps();

            $table->foreign('lead_id')->references('id')->on('leads')->onDelete('cascade');
            $table->foreign('sla_id')->references('id')->on('slas')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_slas');
    }
};
