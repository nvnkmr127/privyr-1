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
        Schema::create('workflow_executions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('workflow_id')->unsigned();
            $table->string('entity_type');
            $table->integer('entity_id')->unsigned();
            $table->string('trigger');
            $table->string('status')->default('pending'); // pending, completed, failed
            $table->text('error')->nullable();
            $table->integer('retry_count')->default(0);
            $table->string('idempotency_key')->unique()->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('workflow_id')->references('id')->on('workflows')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('workflow_executions');
    }
};
