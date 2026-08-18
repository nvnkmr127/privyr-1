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
        Schema::table('leads', function (Blueprint $table) {
            $table->string('next_action')->nullable();
            $table->unsignedInteger('follow_up_owner_id')->nullable();
            $table->foreign('follow_up_owner_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['follow_up_owner_id']);
            $table->dropColumn([
                'next_action',
                'follow_up_owner_id',
            ]);
        });
    }
};
