<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lead_source_connectors', function (Blueprint $table) {
            $table->string('meta_page_id')->nullable()->after('api_key');
            $table->string('meta_page_name')->nullable()->after('meta_page_id');
            // Page access token obtained via the Facebook "Connect" OAuth flow.
            // Stored encrypted via the model's cast.
            $table->text('meta_page_access_token')->nullable()->after('meta_page_name');
        });
    }

    public function down(): void
    {
        Schema::table('lead_source_connectors', function (Blueprint $table) {
            $table->dropColumn(['meta_page_id', 'meta_page_name', 'meta_page_access_token']);
        });
    }
};
