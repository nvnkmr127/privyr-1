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
        Schema::create('lead_assignment_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // direct, round_robin, weighted
            $table->boolean('status')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('lead_assignment_rule_conditions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rule_id');
            $table->string('attribute');
            $table->string('operator');
            $table->text('value')->nullable();
            $table->timestamps();

            $table->foreign('rule_id')->references('id')->on('lead_assignment_rules')->onDelete('cascade');
        });

        Schema::create('lead_assignment_rule_users', function (Blueprint $table) {
            $table->unsignedBigInteger('rule_id');
            $table->unsignedInteger('user_id');
            $table->integer('weight')->default(1);
            $table->timestamp('last_assigned_at')->nullable();

            $table->foreign('rule_id')->references('id')->on('lead_assignment_rules')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->primary(['rule_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_assignment_rule_users');
        Schema::dropIfExists('lead_assignment_rule_conditions');
        Schema::dropIfExists('lead_assignment_rules');
    }
};
