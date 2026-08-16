<?php

namespace Webkul\Moldable\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Webkul\Moldable\Models\Workspace;
use Webkul\Moldable\Models\WorkspaceMember;

class ResolveWorkspace
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Authentication required.'], 401);
        }

        // Precedence: an explicit header/route id (a deliberate request) is
        // strictly enforced; a session selection (the tenant switcher) is used
        // when present but falls back silently if it has gone stale.
        $explicitId = $request->header('X-Workspace-Id') ?: $request->route('workspace');
        $sessionId = $request->session()->get('current_workspace_id');

        $workspace = null;

        if ($explicitId) {
            $workspace = $this->resolveMemberWorkspace($explicitId, $user);

            if (! $workspace) {
                return response()->json(['message' => 'You do not have access to this workspace.'], 403);
            }
        } elseif ($sessionId) {
            // Stale/invalid session selection must not hard-fail the request.
            $workspace = $this->resolveMemberWorkspace($sessionId, $user);
        }

        if (! $workspace) {
            // Fall back to the user's default workspace, creating a personal one
            // on first use so workspace-aware pages work without provisioning.
            $workspace = $this->resolveDefaultWorkspace($user);
        }

        // Persist the resolved tenant so plain page GETs (which cannot send the
        // header) stay scoped to the selected workspace across requests.
        $request->session()->put('current_workspace_id', $workspace->id);

        $request->attributes->set('moldable_workspace', $workspace);

        return $next($request);
    }

    /**
     * Return the workspace only if it is active and the user is an active member,
     * else null. Never trust the id without this membership check.
     */
    private function resolveMemberWorkspace($workspaceId, $user): ?Workspace
    {
        $workspace = Workspace::query()
            ->whereKey($workspaceId)
            ->where('is_active', true)
            ->first();

        if (! $workspace) {
            return null;
        }

        $isMember = WorkspaceMember::query()
            ->where('workspace_id', $workspace->id)
            ->where('user_id', $user->getAuthIdentifier())
            ->where('is_active', true)
            ->exists();

        return $isMember ? $workspace : null;
    }

    private function resolveDefaultWorkspace($user): Workspace
    {
        $userId = $user->getAuthIdentifier();

        $membership = WorkspaceMember::query()
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->orderBy('workspace_id')
            ->first();

        if ($membership) {
            $workspace = Workspace::whereKey($membership->workspace_id)->where('is_active', true)->first();

            if ($workspace) {
                return $workspace;
            }
        }

        $name = trim((string) ($user->name ?? 'My')).' Workspace';

        $workspace = Workspace::create([
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(5)),
            'settings' => [],
            'is_active' => true,
        ]);

        WorkspaceMember::create([
            'workspace_id' => $workspace->id,
            'user_id' => $userId,
            'role' => 'owner',
            'permissions' => ['*'],
            'is_active' => true,
        ]);

        return $workspace;
    }
}
