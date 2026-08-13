<?php

namespace Webkul\Moldable\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Webkul\Moldable\Models\CustomField;

class BuilderController
{
    public function fields(Request $request): JsonResponse
    {
        $workspace = $request->attributes->get('moldable_workspace');
        $query = $workspace->fields()->where('is_active', true)->orderBy('entity_type')->orderBy('sort_order');

        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->string('entity_type'));
        }

        return response()->json($query->get());
    }

    public function storeField(Request $request): JsonResponse
    {
        $workspace = $request->attributes->get('moldable_workspace');
        $data = $request->validate([
            'entity_type' => ['required', 'string', 'max:80'],
            'key' => ['required', 'string', 'max:120', 'alpha_dash'],
            'label' => ['required', 'string', 'max:160'],
            'type' => ['required', 'in:text,textarea,number,currency,date,datetime,boolean,select,multiselect,email,phone,url'],
            'options' => ['nullable', 'array'],
            'config' => ['nullable', 'array'],
            'group_name' => ['nullable', 'string', 'max:120'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_required' => ['nullable', 'boolean'],
        ]);

        $field = CustomField::create([
            ...$data,
            'workspace_id' => $workspace->id,
            'options' => $data['options'] ?? [],
            'config' => $data['config'] ?? [],
            'sort_order' => $data['sort_order'] ?? 0,
            'is_required' => $data['is_required'] ?? false,
            'is_active' => true,
        ]);

        return response()->json($field, 201);
    }

    public function updateField(Request $request, CustomField $field): JsonResponse
    {
        $workspace = $request->attributes->get('moldable_workspace');
        abort_unless($field->workspace_id === $workspace->id, 404);

        $data = $request->validate([
            'label' => ['sometimes', 'string', 'max:160'],
            'type' => ['sometimes', 'in:text,textarea,number,currency,date,datetime,boolean,select,multiselect,email,phone,url'],
            'options' => ['sometimes', 'array'],
            'config' => ['sometimes', 'array'],
            'group_name' => ['nullable', 'string', 'max:120'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'is_required' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $field->update($data);

        return response()->json($field->fresh());
    }

    public function deleteField(Request $request, CustomField $field): JsonResponse
    {
        $workspace = $request->attributes->get('moldable_workspace');
        abort_unless($field->workspace_id === $workspace->id, 404);
        $field->update(['is_active' => false]);

        return response()->json(['message' => 'Field archived.']);
    }
}
