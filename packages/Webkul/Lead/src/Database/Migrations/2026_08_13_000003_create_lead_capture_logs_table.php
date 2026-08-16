<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lead_capture_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('connector_id')->nullable();
            $table->json('raw_payload')->nullable();
            $table->string('status')->default('success'); // success, duplicate_flagged, error
            $table->unsignedInteger('lead_id')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->foreign('connector_id')->references('id')->on('lead_source_connectors')->onDelete('cascade');
            $table->foreign('lead_id')->references('id')->on('leads')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lead_capture_logs');
    }
};
