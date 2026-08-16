<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Stores per-device push tokens for agents so a new lead can buzz the
     * assigned agent's phone within seconds — the product's headline promise.
     */
    public function up(): void
    {
        Schema::create('device_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->string('token');                 // FCM/Expo registration token
            $table->string('platform')->default('android'); // android|ios|web
            $table->string('device_name')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            // A device token is globally unique — re-registering it under a new
            // agent simply moves ownership (handled via updateOrCreate on token).
            $table->unique('token');

            // users.id is INT unsigned in Krayin core, so the FK column matches.
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_tokens');
    }
};
