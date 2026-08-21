<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('workflow_executions', function (Blueprint $table) {
            $table->string('chain_id')->nullable()->index();
            $table->integer('depth')->default(0);
        });
    }

    public function down()
    {
        Schema::table('workflow_executions', function (Blueprint $table) {
            $table->dropColumn(['chain_id', 'depth']);
        });
    }
};
