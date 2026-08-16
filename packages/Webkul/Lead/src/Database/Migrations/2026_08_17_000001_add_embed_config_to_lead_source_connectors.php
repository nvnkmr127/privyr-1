<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-connector embed/hosted-form configuration (title, button text,
     * redirect URL, which fields to show). Nullable + backward compatible:
     * existing connectors fall back to sensible defaults in the hosted form.
     */
    public function up(): void
    {
        Schema::table('lead_source_connectors', function (Blueprint $table) {
            $table->json('embed_config')->nullable()->after('field_mappings');
        });
    }

    public function down(): void
    {
        Schema::table('lead_source_connectors', function (Blueprint $table) {
            $table->dropColumn('embed_config');
        });
    }
};
