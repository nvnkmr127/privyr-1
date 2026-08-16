<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tenant isolation for the lead-capture system. The tenant is a Moldable
 * workspace (moldable_workspaces). Every connector belongs to a workspace, and
 * every captured lead / person / log is stamped with the owning workspace so
 * tenant-owned data can be scoped and never leaks across tenants.
 *
 * All columns are nullable so pre-tenancy rows remain valid; new writes always
 * set the workspace. webhook_token stays globally unique — it is the public
 * embed identifier and must resolve to exactly one connector (hence one tenant).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lead_source_connectors', function (Blueprint $table) {
            $table->unsignedBigInteger('workspace_id')->nullable()->after('id');
            $table->index('workspace_id');
            $table->foreign('workspace_id')->references('id')->on('moldable_workspaces')->nullOnDelete();
        });

        // Captured data is tenant-owned. Indexed (no hard FK) to avoid coupling
        // core CRM tables to the workspace table's lifecycle.
        foreach (['leads', 'persons', 'lead_capture_logs'] as $tableName) {
            if (Schema::hasColumn($tableName, 'workspace_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->unsignedBigInteger('workspace_id')->nullable()->index();
            });
        }
    }

    public function down(): void
    {
        Schema::table('lead_source_connectors', function (Blueprint $table) {
            $table->dropForeign(['workspace_id']);
            $table->dropIndex(['workspace_id']);
            $table->dropColumn('workspace_id');
        });

        foreach (['leads', 'persons', 'lead_capture_logs'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('workspace_id');
            });
        }
    }
};
