<?php

namespace Webkul\Moldable\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Webkul\Moldable\Models\SavedView;

class ViewController
{
    public function index(Request $request): JsonResponse
    {
        $workspace = $request->attributes->get('moldable_workspace');
        $query = $workspace->views()->orderByDesc('is_default')->orderBy('name');

        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->string('entity_type'));
        }

        $views = $query->get()->filter(function (SavedView $view) use ($request) {
            return $view->visibility !== 'private' || (string) $view->user_id === (string) $request->user()->getAuthIdentifier();
        })->values();

        return response()->json($views);
    }

    public function store(Request $request): JsonResponse
    {
        $workspace = $request->attributes->get('moldable_workspace');
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'entity_type' => ['required', 'string', 'max:80'],
            'filters' => ['nullable', 'array'],
            'columns' => ['nullable', 'array'],
            'sort' => ['nullable', 'array'],
            'group_by' => ['nullable', 'array'],
            'visibility' => ['nullable', 'in:private,team,workspace'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        if (($data['is_default'] ?? false) === true) {
            $workspace->views()->where('entity_type', $data['entity_type'])->update(['is_default' => false]);
        }

        $view = SavedView::create([
            ...$data,
            'workspace_id' => $workspace->id,
            'user_id' => $request->user()->getAuthIdentifier(),
            'filters' => $data['filters'] ?? [],
            'columns' => $data['columns'] ?? [],
            'sort' => $data['sort'] ?? [],
            'group_by' => $data['group_by'] ?? [],
            'visibility' => $data['visibility'] ?? 'private',
            'is_default' => $data['is_default'] ?? false,
        ]);

        return response()->json($view, 201);
    }

    public function update(Request $request, SavedView $view): JsonResponse
    {
        $workspace = $request->attributes->get('moldable_workspace');
        abort_unless($view->workspace_id === $workspace->id, 404);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:160'],
            'filters' => ['sometimes', 'array'],
            'columns' => ['sometimes', 'array'],
            'sort' => ['sometimes', 'array'],
            'group_by' => ['sometimes', 'array'],
            'visibility' => ['sometimes', 'in:private,team,workspace'],
            'is_default' => ['sometimes', 'boolean'],
        ]);

        if (($data['is_default'] ?? false) === true) {
            $workspace->views()->where('entity_type', $view->entity_type)->whereKeyNot($view->id)->update(['is_default' => false]);
        }

        $view->update($data);

        return response()->json($view->fresh());
    }

    public function destroy(Request $request, SavedView $view): JsonResponse
    {
        abort_unless($view->workspace_id === $request->attributes->get('moldable_workspace')->id, 404);
        $view->delete();

        return response()->json(['message' => 'View deleted.']);
    }
}
