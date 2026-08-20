<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('workflows', function (Blueprint $table) {
            $table->string('status')->default('active')->after('entity_type');
            $table->boolean('is_scheduled')->default(0)->after('status');
            $table->string('schedule_frequency')->nullable()->after('is_scheduled');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('workflows', function (Blueprint $table) {
            $table->dropColumn(['status', 'is_scheduled', 'schedule_frequency']);
        });
    }
};
