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
        Schema::disableForeignKeyConstraints();

        if (Schema::hasColumn('leads', 'person_id')) {
            Schema::table('leads', function (Blueprint $table) {
                if (Schema::hasTable('persons')) {
                    $table->dropForeign(['person_id']);
                }
                $table->dropColumn('person_id');
            });
        }

        if (Schema::hasTable('quotes') && Schema::hasColumn('quotes', 'person_id')) {
            Schema::table('quotes', function (Blueprint $table) {
                if (Schema::hasTable('persons')) {
                    $table->dropForeign(['person_id']);
                }
                $table->dropColumn('person_id');
            });
        }

        if (Schema::hasColumn('emails', 'person_id')) {
            Schema::table('emails', function (Blueprint $table) {
                if (Schema::hasTable('persons')) {
                    $table->dropForeign(['person_id']);
                }
                $table->dropColumn('person_id');
            });
        }

        Schema::dropIfExists('contact_export_batch_items');
        Schema::dropIfExists('person_activities');
        Schema::dropIfExists('person_tags');
        Schema::dropIfExists('persons');
        Schema::dropIfExists('organizations');

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // One-way migration
    }
};
