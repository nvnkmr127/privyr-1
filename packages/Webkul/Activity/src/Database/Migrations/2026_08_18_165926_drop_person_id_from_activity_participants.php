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
        if (Schema::hasColumn('activity_participants', 'person_id')) {
            Schema::table('activity_participants', function (Blueprint $table) {
                if (Schema::hasTable('persons')) {
                    $table->dropForeign(['person_id']);
                }
                $table->dropColumn('person_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // One-way migration
    }
};
