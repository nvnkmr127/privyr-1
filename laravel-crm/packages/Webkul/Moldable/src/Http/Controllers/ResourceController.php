<?php

namespace Webkul\Moldable\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Webkul\Moldable\Models\WorkspaceResource;

class ResourceController
{
    public function attach(Request $request): JsonResponse
    {
        $workspace = $request->attributes->get('moldable_workspace');
        $data = $request->validate([
            'resource_type' => ['required', 'string', 'max:80'],
            'resource_id' => ['required', 'integer'],
        ]);

        $binding = WorkspaceResource::firstOrCreate([
            'workspace_id' => $workspace->id,
            'resource_type' => $data['resource_type'],
            'resource_id' => $data['resource_id'],
        ]);

        return response()->json($binding, 201);
    }

    public function detach(Request $request): JsonResponse
    {
        $workspace = $request->attributes->get('moldable_workspace');
        $data = $request->validate([
            'resource_type' => ['required', 'string', 'max:80'],
            'resource_id' => ['required', 'integer'],
        ]);

        WorkspaceResource::where('workspace_id', $workspace->id)
            ->where('resource_type', $data['resource_type'])
            ->where('resource_id', $data['resource_id'])
            ->delete();

        return response()->json(['message' => 'Resource detached.']);
    }

    public function index(Request $request): JsonResponse
    {
        $workspace = $request->attributes->get('moldable_workspace');
        $query = WorkspaceResource::where('workspace_id', $workspace->id);

        if ($request->filled('resource_type')) {
            $query->where('resource_type', $request->string('resource_type'));
        }

        return response()->json($query->latest()->paginate(min((int) $request->input('per_page', 50), 200)));
    }
}
