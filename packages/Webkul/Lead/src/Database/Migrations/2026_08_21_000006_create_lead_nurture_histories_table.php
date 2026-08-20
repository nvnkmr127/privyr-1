<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_nurture_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('lead_id');
            $table->string('previous_status')->nullable();
            $table->unsignedInteger('nurture_reason_id')->nullable();
            $table->timestamp('started_at');
            $table->date('expected_reengagement_date')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->string('end_reason')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('lead_id')->references('id')->on('leads')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('nurture_reason_id')->references('id')->on('attribute_options')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_nurture_histories');
    }
};
