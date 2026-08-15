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

        $workspaceId = $request->header('X-Workspace-Id') ?: $request->route('workspace');

        if ($workspaceId) {
            $workspace = Workspace::query()
                ->whereKey($workspaceId)
                ->where('is_active', true)
                ->first();

            $isMember = $workspace && WorkspaceMember::query()
                ->where('workspace_id', $workspace->id)
                ->where('user_id', $user->getAuthIdentifier())
                ->where('is_active', true)
                ->exists();

            if (! $isMember) {
                return response()->json(['message' => 'You do not have access to this workspace.'], 403);
            }
        } else {
            // No workspace supplied: fall back to the user's default workspace,
            // creating a personal one on first use so the builder works without
            // a separate workspace-provisioning step.
            $workspace = $this->resolveDefaultWorkspace($user);
        }

        $request->attributes->set('moldable_workspace', $workspace);

        return $next($request);
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
            'name'      => $name,
            'slug'      => Str::slug($name).'-'.Str::lower(Str::random(5)),
            'settings'  => [],
            'is_active' => true,
        ]);

        WorkspaceMember::create([
            'workspace_id' => $workspace->id,
            'user_id'      => $userId,
            'role'         => 'owner',
            'permissions'  => ['*'],
            'is_active'    => true,
        ]);

        return $workspace;
    }
}
