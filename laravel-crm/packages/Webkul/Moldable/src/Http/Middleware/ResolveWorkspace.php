<?php

namespace Webkul\Moldable\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Webkul\Moldable\Models\Workspace;
use Webkul\Moldable\Models\WorkspaceMember;

class ResolveWorkspace
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $workspaceId = $request->header('X-Workspace-Id') ?: $request->route('workspace');

        if (! $user || ! $workspaceId) {
            return response()->json(['message' => 'Authenticated user and X-Workspace-Id are required.'], 401);
        }

        $workspace = Workspace::query()
            ->whereKey($workspaceId)
            ->where('is_active', true)
            ->first();

        if (! $workspace || ! WorkspaceMember::query()
            ->where('workspace_id', $workspace->id)
            ->where('user_id', $user->getAuthIdentifier())
            ->where('is_active', true)
            ->exists()) {
            return response()->json(['message' => 'You do not have access to this workspace.'], 403);
        }

        $request->attributes->set('moldable_workspace', $workspace);

        return $next($request);
    }
}
