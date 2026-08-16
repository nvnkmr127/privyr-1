<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Backfill: once core CRM data is tenant-scoped, existing rows with a NULL
 * workspace_id would become invisible to non-super-admin users. Assign all such
 * legacy leads / persons / capture logs to a single "Primary Workspace" and make
 * every existing user a member, so nothing disappears when scoping turns on.
 *
 * Idempotent and data-safe: only touches NULL rows and only adds memberships
 * that don't already exist.
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        // Reuse the lowest-id active workspace as "primary", else create one.
        $workspaceId = DB::table('moldable_workspaces')->where('is_active', true)->min('id');

        if (! $workspaceId) {
            $workspaceId = DB::table('moldable_workspaces')->insertGetId([
                'name' => 'Primary Workspace',
                'slug' => 'primary-'.Str::lower(Str::random(6)),
                'settings' => json_encode([]),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Every existing user becomes a member of the primary workspace.
        foreach (DB::table('users')->pluck('id') as $userId) {
            $exists = DB::table('moldable_workspace_members')
                ->where('workspace_id', $workspaceId)
                ->where('user_id', $userId)
                ->exists();

            if (! $exists) {
                DB::table('moldable_workspace_members')->insert([
                    'workspace_id' => $workspaceId,
                    'user_id' => $userId,
                    'role' => 'member',
                    'permissions' => json_encode(['*']),
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // Assign legacy (untenanted) core data to the primary workspace.
        foreach (['leads', 'persons', 'lead_capture_logs'] as $table) {
            DB::table($table)->whereNull('workspace_id')->update(['workspace_id' => $workspaceId]);
        }
    }

    public function down(): void
    {
        // Non-reversible data backfill; nothing to undo safely.
    }
};
