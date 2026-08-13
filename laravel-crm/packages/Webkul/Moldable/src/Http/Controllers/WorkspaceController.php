<?php

namespace Webkul\Moldable\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Webkul\Moldable\Models\Team;
use Webkul\Moldable\Models\Workspace;
use Webkul\Moldable\Models\WorkspaceMember;

class WorkspaceController
{
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->getAuthIdentifier();

        $workspaces = Workspace::query()
            ->whereHas('members', fn ($query) => $query->where('user_id', $userId)->where('is_active', true))
            ->withCount('members')
            ->orderBy('name')
            ->get();

        return response()->json($workspaces);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:120', 'alpha_dash', 'unique:moldable_workspaces,slug'],
            'settings' => ['nullable', 'array'],
        ]);

        $workspace = Workspace::create([
            'name' => $data['name'],
            'slug' => $data['slug'] ?? Str::slug($data['name']).'-'.Str::lower(Str::random(5)),
            'settings' => $data['settings'] ?? [],
            'is_active' => true,
        ]);

        WorkspaceMember::create([
            'workspace_id' => $workspace->id,
            'user_id' => $request->user()->getAuthIdentifier(),
            'role' => 'owner',
            'permissions' => ['*'],
            'is_active' => true,
        ]);

        return response()->json($workspace->loadCount('members'), 201);
    }

    public function show(Request $request): JsonResponse
    {
        $workspace = $request->attributes->get('moldable_workspace');

        return response()->json($workspace->loadCount('members')->load(['teams', 'views']));
    }

    public function addMember(Request $request): JsonResponse
    {
        $workspace = $request->attributes->get('moldable_workspace');
        $this->requireRole($request, $workspace, ['owner', 'admin']);

        $data = $request->validate([
            'user_id' => ['required', 'integer'],
            'role' => ['nullable', 'in:owner,admin,manager,member,viewer'],
            'team_id' => ['nullable', 'integer'],
            'permissions' => ['nullable', 'array'],
        ]);

        if (! empty($data['team_id'])) {
            abort_unless(Team::where('workspace_id', $workspace->id)->whereKey($data['team_id'])->exists(), 422, 'Team does not belong to this workspace.');
        }

        $member = WorkspaceMember::updateOrCreate(
            ['workspace_id' => $workspace->id, 'user_id' => $data['user_id']],
            [
                'role' => $data['role'] ?? 'member',
                'team_id' => $data['team_id'] ?? null,
                'permissions' => $data['permissions'] ?? [],
                'is_active' => true,
            ]
        );

        return response()->json($member, 201);
    }

    public function createTeam(Request $request): JsonResponse
    {
        $workspace = $request->attributes->get('moldable_workspace');
        $this->requireRole($request, $workspace, ['owner', 'admin', 'manager']);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:120', 'alpha_dash'],
            'description' => ['nullable', 'string'],
            'settings' => ['nullable', 'array'],
        ]);

        $slug = $data['slug'] ?? Str::slug($data['name']);
        if (Team::where('workspace_id', $workspace->id)->where('slug', $slug)->exists()) {
            $slug .= '-'.Str::lower(Str::random(4));
        }

        return response()->json(Team::create([
            'workspace_id' => $workspace->id,
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'settings' => $data['settings'] ?? [],
            'is_active' => true,
        ]), 201);
    }

    public function teams(Request $request): JsonResponse
    {
        return response()->json($request->attributes->get('moldable_workspace')->teams()->withCount('members')->orderBy('name')->get());
    }

    private function requireRole(Request $request, Workspace $workspace, array $roles): void
    {
        $allowed = WorkspaceMember::query()
            ->where('workspace_id', $workspace->id)
            ->where('user_id', $request->user()->getAuthIdentifier())
            ->where('is_active', true)
            ->whereIn('role', $roles)
            ->exists();

        abort_unless($allowed, 403, 'You do not have permission to manage this workspace.');
    }
}
