<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
            // First Touch
            $table->integer('first_lead_source_id')->unsigned()->nullable();
            $table->string('first_origin')->nullable();
            $table->string('first_campaign')->nullable();
            $table->string('first_medium')->nullable();
            $table->string('first_content')->nullable();
            $table->string('first_term')->nullable();
            $table->string('first_landing_page')->nullable();
            $table->string('first_form')->nullable();
            $table->string('first_external_source')->nullable();
            $table->string('first_external_id')->nullable();

            // Latest Touch
            $table->integer('latest_lead_source_id')->unsigned()->nullable();
            $table->string('latest_origin')->nullable();
            $table->string('latest_campaign')->nullable();
            $table->string('latest_medium')->nullable();
            $table->string('latest_content')->nullable();
            $table->string('latest_term')->nullable();
            $table->string('latest_landing_page')->nullable();
            $table->string('latest_form')->nullable();
            $table->string('latest_external_source')->nullable();
            $table->string('latest_external_id')->nullable();

            // Foreign keys
            $table->foreign('first_lead_source_id')->references('id')->on('lead_sources')->onDelete('set null');
            $table->foreign('latest_lead_source_id')->references('id')->on('lead_sources')->onDelete('set null');

            // Indexes
            $table->index('first_lead_source_id');
            $table->index('first_origin');
            $table->index('latest_lead_source_id');
            $table->index('latest_origin');
            $table->index('latest_external_id');
        });

        // Migrate existing data
        DB::statement('
            UPDATE leads 
            SET 
                first_lead_source_id = lead_source_id,
                first_origin = origin,
                first_external_id = external_id,
                first_campaign = COALESCE(campaign, utm_campaign),
                first_medium = utm_medium,
                first_external_source = utm_source,
                
                latest_lead_source_id = lead_source_id,
                latest_origin = origin,
                latest_external_id = external_id,
                latest_campaign = COALESCE(campaign, utm_campaign),
                latest_medium = utm_medium,
                latest_external_source = utm_source
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['first_lead_source_id']);
            $table->dropForeign(['latest_lead_source_id']);

            $table->dropColumn([
                'first_lead_source_id',
                'first_origin',
                'first_campaign',
                'first_medium',
                'first_content',
                'first_term',
                'first_landing_page',
                'first_form',
                'first_external_source',
                'first_external_id',
                'latest_lead_source_id',
                'latest_origin',
                'latest_campaign',
                'latest_medium',
                'latest_content',
                'latest_term',
                'latest_landing_page',
                'latest_form',
                'latest_external_source',
                'latest_external_id',
            ]);
        });
    }
};
