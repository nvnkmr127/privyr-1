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
        Schema::create('lead_attribution_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('lead_id')->unsigned();
            $table->integer('lead_source_id')->unsigned()->nullable();
            
            $table->string('origin')->nullable();
            $table->string('campaign')->nullable();
            $table->string('medium')->nullable();
            $table->string('content')->nullable();
            $table->string('term')->nullable();
            $table->string('landing_page')->nullable();
            $table->string('form')->nullable();
            $table->string('external_source')->nullable();
            $table->string('external_id')->nullable();

            $table->timestamps(); // created_at serves as recorded_at

            $table->foreign('lead_id')->references('id')->on('leads')->onDelete('cascade');
            $table->foreign('lead_source_id')->references('id')->on('lead_sources')->onDelete('set null');

            $table->index(['lead_id', 'created_at']);
            $table->index('lead_source_id');
            $table->index('origin');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lead_attribution_histories');
    }
};
