<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('moldable_custom_fields');
    }

    public function down(): void
    {
        // The existing Attribute module owns custom field storage.
    }
};
