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
        Schema::table('lead_capture_logs', function (Blueprint $table) {
            $table->string('origin')->nullable()->after('connector_id');
            $table->string('external_id')->nullable()->after('origin');
            $table->unsignedInteger('processing_time_ms')->nullable()->after('error_message');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lead_capture_logs', function (Blueprint $table) {
            $table->dropColumn(['origin', 'external_id', 'processing_time_ms']);
        });
    }
};
