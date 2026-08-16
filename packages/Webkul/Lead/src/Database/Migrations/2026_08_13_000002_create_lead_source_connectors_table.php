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
        Schema::create('lead_source_connectors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('source_type'); // manual, qr, shareable_form, webform, meta_ads, google_ads, indiamart, justdial, realestate_99acres, magicbricks, housing, sulekha, webhook, rest_api, zapier
            $table->string('webhook_token')->unique();
            $table->string('api_key')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('duplicate_action')->default('update'); // skip, update, create_new_tag, attach_contact
            $table->json('field_mappings')->nullable();
            $table->unsignedInteger('default_lead_pipeline_id')->nullable();
            $table->unsignedInteger('default_lead_pipeline_stage_id')->nullable();
            $table->unsignedInteger('default_user_id')->nullable();
            $table->unsignedInteger('captured_count')->default(0);
            $table->timestamp('last_received_at')->nullable();
            $table->timestamps();

            $table->foreign('default_lead_pipeline_id')->references('id')->on('lead_pipelines')->onDelete('set null');
            $table->foreign('default_lead_pipeline_stage_id')->references('id')->on('lead_pipeline_stages')->onDelete('set null');
            $table->foreign('default_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lead_source_connectors');
    }
};
