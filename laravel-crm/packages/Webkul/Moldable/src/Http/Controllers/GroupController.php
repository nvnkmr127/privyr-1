<?php

namespace Webkul\Moldable\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Webkul\Attribute\Models\Attribute;
use Webkul\Moldable\Models\FieldGroup;
use Webkul\Moldable\Models\FieldGroupAttribute;

class GroupController
{
    public function index(Request $request): JsonResponse
    {
        $workspace = $request->attributes->get('moldable_workspace');
        $entityType = $request->input('entity_type', 'leads');

        $groups = FieldGroup::where('workspace_id', $workspace->id)
            ->where('entity_type', $entityType)
            ->with(['groupAttributes.attribute'])
            ->orderBy('sort_order')
            ->get();

        return response()->json($groups);
    }

    public function store(Request $request): JsonResponse
    {
        $workspace = $request->attributes->get('moldable_workspace');
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'entity_type' => ['nullable', 'string', 'max:80'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $entityType = $data['entity_type'] ?? 'leads';
        $slug = Str::slug($data['name']);

        if (FieldGroup::where('workspace_id', $workspace->id)->where('entity_type', $entityType)->where('slug', $slug)->exists()) {
            return response()->json(['message' => 'A field group with this name already exists.'], 422);
        }

        $group = FieldGroup::create([
            'workspace_id' => $workspace->id,
            'entity_type' => $entityType,
            'name' => $data['name'],
            'slug' => $slug,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return response()->json($group, 201);
    }

    public function update(Request $request, FieldGroup $group): JsonResponse
    {
        $workspace = $request->attributes->get('moldable_workspace');
        abort_unless($group->workspace_id === $workspace->id, 404);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:120'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ]);

        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $group->update($data);

        return response()->json($group->fresh());
    }

    public function destroy(Request $request, FieldGroup $group): JsonResponse
    {
        $workspace = $request->attributes->get('moldable_workspace');
        abort_unless($group->workspace_id === $workspace->id, 404);

        // Delete group presentation wrapper without deleting the core Attribute records
        $group->delete();

        return response()->json(['message' => 'Field group removed without deleting attributes.']);
    }

    public function assignAttributes(Request $request, FieldGroup $group): JsonResponse
    {
        $workspace = $request->attributes->get('moldable_workspace');
        abort_unless($group->workspace_id === $workspace->id, 404);

        $data = $request->validate([
            'attribute_ids' => ['required', 'array'],
            'attribute_ids.*' => ['integer', 'exists:attributes,id'],
        ]);

        $attributeIds = array_values(array_unique($data['attribute_ids']));

        // Every assigned field must belong to the same entity as the group.
        $matching = Attribute::whereIn('id', $attributeIds)
            ->where('entity_type', $group->entity_type)
            ->count();

        if ($matching !== count($attributeIds)) {
            return response()->json(['message' => 'All fields must belong to the same entity as the group.'], 422);
        }

        $group->groupAttributes()->delete();

        foreach ($attributeIds as $order => $attributeId) {
            FieldGroupAttribute::create([
                'group_id' => $group->id,
                'attribute_id' => $attributeId,
                'sort_order' => $order,
            ]);
        }

        return response()->json($group->fresh(['groupAttributes.attribute']));
    }
}
