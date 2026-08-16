<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tenant isolation for contacts. `persons.unique_id` (email|phone) had a GLOBAL
 * unique index, so two tenants could never both capture the same contact — a
 * cross-tenant collision. Scope uniqueness to (workspace_id, unique_id) so each
 * tenant owns an independent contact space.
 *
 * unique_id is only ever generated + stored by PersonRepository (never used as a
 * lookup key), so loosening the global constraint changes no application logic.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            $table->dropUnique('persons_unique_id_unique');
            $table->unique(['workspace_id', 'unique_id'], 'persons_workspace_unique_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            $table->dropUnique('persons_workspace_unique_id_unique');
            $table->unique('unique_id', 'persons_unique_id_unique');
        });
    }
};
