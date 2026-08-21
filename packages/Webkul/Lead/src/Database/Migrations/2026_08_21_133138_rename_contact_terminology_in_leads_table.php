<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->renameColumn('person_name', 'name');
            $table->renameColumn('organization_name', 'organization');
            $table->renameColumn('contact_numbers', 'phones');
        });

        DB::table('attributes')->where('code', 'person_name')->update(['code' => 'name']);
        DB::table('attributes')->where('code', 'organization_name')->update(['code' => 'organization']);
        DB::table('attributes')->where('code', 'contact_numbers')->update(['code' => 'phones']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('attributes')->where('code', 'name')->update(['code' => 'person_name']);
        DB::table('attributes')->where('code', 'organization')->update(['code' => 'organization_name']);
        DB::table('attributes')->where('code', 'phones')->update(['code' => 'contact_numbers']);

        Schema::table('leads', function (Blueprint $table) {
            $table->renameColumn('name', 'person_name');
            $table->renameColumn('organization', 'organization_name');
            $table->renameColumn('phones', 'contact_numbers');
        });
    }
};
