<?php

namespace App\Support;

use Webkul\Moldable\Models\WorkspaceMember;

/**
 * Resolves the current tenant (Moldable workspace) for the authenticated admin
 * session, for use by tenant-scoping query logic. Bound as a singleton so the
 * result is memoized for the whole request.
 *
 * Returns null — meaning "do not scope" — when:
 *   - there is no authenticated admin (console, queue jobs, public webhooks), or
 *   - the user is a super-admin (Administrator role → sees all workspaces).
 */
class WorkspaceContext
{
    protected bool $resolved = false;

    protected ?int $workspaceId = null;

    public function currentWorkspaceId(): ?int
    {
        if ($this->resolved) {
            return $this->workspaceId;
        }

        $this->resolved = true;

        return $this->workspaceId = $this->resolve();
    }

    public function isSuperAdmin($user = null): bool
    {
        $user ??= auth()->guard('user')->user();

        return $user && optional($user->role)->permission_type === 'all';
    }

    protected function resolve(): ?int
    {
        $user = auth()->guard('user')->user();

        if (! $user) {
            return null; // console / queue / public request — no tenant scoping
        }

        if ($this->isSuperAdmin($user)) {
            return null; // super-admin sees every workspace
        }

        $request = app()->bound('request') ? request() : null;

        // Prefer a workspace already resolved by the ResolveWorkspace middleware.
        if ($request && ($ws = $request->attributes->get('moldable_workspace'))) {
            return $ws->id;
        }

        $userId = $user->getAuthIdentifier();

        // Session selection (the tenant switcher), validated against membership.
        $sessionId = $request && $request->hasSession() ? $request->session()->get('current_workspace_id') : null;

        if ($sessionId && $this->isMember($userId, (int) $sessionId)) {
            return (int) $sessionId;
        }

        // Fall back to the user's default (lowest-id) active membership.
        return WorkspaceMember::where('user_id', $userId)
            ->where('is_active', true)
            ->orderBy('workspace_id')
            ->value('workspace_id');
    }

    protected function isMember($userId, int $workspaceId): bool
    {
        return WorkspaceMember::where('user_id', $userId)
            ->where('workspace_id', $workspaceId)
            ->where('is_active', true)
            ->exists();
    }
}
