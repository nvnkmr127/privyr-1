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
            $table->string('normalized_primary_email')->nullable()->index();
            $table->string('normalized_primary_phone')->nullable()->index();
            $table->string('duplicate_status')->default('clean')->index();
            $table->integer('duplicate_of_id')->unsigned()->nullable();
            $table->foreign('duplicate_of_id')->references('id')->on('leads')->nullOnDelete();
            
            $table->boolean('is_merged')->default(false)->index();
            $table->integer('merged_into_id')->unsigned()->nullable();
            $table->foreign('merged_into_id')->references('id')->on('leads')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['duplicate_of_id']);
            $table->dropForeign(['merged_into_id']);
            $table->dropColumn([
                'normalized_primary_email',
                'normalized_primary_phone',
                'duplicate_status',
                'duplicate_of_id',
                'is_merged',
                'merged_into_id'
            ]);
        });
    }
};
