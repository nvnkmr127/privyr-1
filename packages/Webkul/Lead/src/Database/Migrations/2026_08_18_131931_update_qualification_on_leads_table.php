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
        if (Schema::hasColumn('leads', 'is_qualified')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->dropColumn('is_qualified');
            });
        }
        if (!Schema::hasColumn('leads', 'qualification_status')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->string('qualification_status')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn('qualification_status');
            $table->boolean('is_qualified')->default(false);
        });
    }
};
