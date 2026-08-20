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
        Schema::table('leads', function (Blueprint $table) {
            $table->string('origin')->nullable()->index();
            $table->string('external_id')->nullable()->index();
            $table->string('ingestion_status')->nullable()->index();
            $table->string('campaign')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['origin', 'external_id', 'ingestion_status', 'campaign']);
        });
    }
};
