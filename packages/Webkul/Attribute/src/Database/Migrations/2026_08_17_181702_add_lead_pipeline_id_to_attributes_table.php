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
        Schema::table('attributes', function (Blueprint $table) {
            $table->integer('lead_pipeline_id')->unsigned()->nullable()->after('entity_type');

            $table->foreign('lead_pipeline_id')
                ->references('id')
                ->on('lead_pipelines')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attributes', function (Blueprint $table) {
            $table->dropForeign(['lead_pipeline_id']);
            $table->dropColumn('lead_pipeline_id');
        });
    }
};
