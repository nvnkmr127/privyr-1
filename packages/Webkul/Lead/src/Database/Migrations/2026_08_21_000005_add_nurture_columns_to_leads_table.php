<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->unsignedInteger('nurture_reason_id')->nullable();
            $table->timestamp('nurtured_at')->nullable();
            $table->date('nurture_reengagement_date')->nullable();
            $table->text('nurture_notes')->nullable();

            $table->foreign('nurture_reason_id')->references('id')->on('attribute_options')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['nurture_reason_id']);
            
            $table->dropColumn([
                'nurture_reason_id',
                'nurtured_at',
                'nurture_reengagement_date',
                'nurture_notes',
            ]);
        });
    }
};
