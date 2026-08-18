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
        Schema::create('lead_score_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('attribute'); // attribute, activity, recency
            $table->json('conditions'); // e.g. {"attribute": "qualification_status", "operator": "==", "value": "qualified"}
            $table->integer('points'); // e.g. +25 or -10
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('lead_score_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('lead_id')->unsigned();
            $table->foreign('lead_id')->references('id')->on('leads')->onDelete('cascade');
            $table->foreignId('rule_id')->constrained('lead_score_rules')->onDelete('cascade');
            $table->integer('points');
            $table->string('reason');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_score_logs');
        Schema::dropIfExists('lead_score_rules');
    }
};
