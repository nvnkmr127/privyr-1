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
        Schema::table('lead_source_connectors', function (Blueprint $table) {
            $table->integer('lead_source_id')->unsigned()->nullable()->after('duplicate_action');
            $table->foreign('lead_source_id')->references('id')->on('lead_sources')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lead_source_connectors', function (Blueprint $table) {
            $table->dropForeign(['lead_source_id']);
            $table->dropColumn('lead_source_id');
        });
    }
};
