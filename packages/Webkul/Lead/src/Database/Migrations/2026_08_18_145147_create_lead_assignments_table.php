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
        Schema::create('lead_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('lead_id');
            $table->unsignedInteger('assigned_to');
            $table->unsignedInteger('assigned_by')->nullable();
            $table->unsignedInteger('previous_owner')->nullable();
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->foreign('lead_id')->references('id')->on('leads')->onDelete('cascade');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('assigned_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('previous_owner')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_assignments');
    }
};
