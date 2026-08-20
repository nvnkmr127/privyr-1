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
        Schema::create('lead_merge_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('surviving_lead_id')->unsigned();
            $table->foreign('surviving_lead_id')->references('id')->on('leads')->onDelete('cascade');
            $table->integer('merged_lead_id')->unsigned();
            // Not cascading delete here because we want to preserve history even if the merged lead is somehow hard deleted later, though it shouldn't be.
            // But if it is deleted, we nullify.
            $table->foreign('merged_lead_id')->references('id')->on('leads')->onDelete('cascade');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->json('merged_data')->nullable(); // Store which fields were moved/selected
            $table->text('merge_reason')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_merge_histories');
    }
};
