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
        Schema::table('activities', function (Blueprint $table) {
            $table->unsignedInteger('lead_id')->nullable()->after('user_id');
            $table->foreign('lead_id')->references('id')->on('leads')->onDelete('cascade');
        });

        // Migrate data
        DB::statement('UPDATE activities a JOIN lead_activities la ON a.id = la.activity_id SET a.lead_id = la.lead_id');

        Schema::dropIfExists('lead_activities');
        Schema::dropIfExists('product_activities');
        Schema::dropIfExists('warehouse_activities');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropForeign(['lead_id']);
            $table->dropColumn('lead_id');
        });
    }
};
