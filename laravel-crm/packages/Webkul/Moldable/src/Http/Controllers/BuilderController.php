<?php

namespace Webkul\Moldable\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Webkul\Attribute\Models\Attribute;
use Webkul\Attribute\Models\AttributeOption;

class BuilderController
{
    public function fields(Request $request): JsonResponse
    {
        $query = Attribute::query()
            ->with('options')
            ->where('is_user_defined', true)
            ->orderBy('entity_type')
            ->orderBy('sort_order')
            ->orderBy('id');

        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->string('entity_type'));
        }

        return response()->json($query->get());
    }

    public function storeField(Request $request): JsonResponse
    {
        $data = $request->validate([
            'entity_type' => ['required', 'string', 'max:80'],
            'code' => ['nullable', 'string', 'max:120', 'alpha_dash'],
            'name' => ['required', 'string', 'max:160'],
            'type' => ['required', 'in:text,textarea,price,boolean,select,multiselect,checkbox,email,address,phone,lookup,datetime,date,file,image'],
            'lookup_type' => ['nullable', 'string', 'max:80'],
            'validation' => ['nullable', 'string', 'max:255'],
            'is_required' => ['nullable', 'boolean'],
            'is_unique' => ['nullable', 'boolean'],
            'quick_add' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'options' => ['nullable', 'array'],
            'options.*.name' => ['required_with:options', 'string', 'max:160'],
            'options.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $code = Str::limit($data['code'] ?? Str::snake($data['name']), 120, '');

        if (Attribute::where('code', $code)->where('entity_type', $data['entity_type'])->exists()) {
            return response()->json(['message' => 'An attribute with this code already exists for this entity.'], 422);
        }

        $attribute = Attribute::create([
            'code' => $code,
            'name' => $data['name'],
            'type' => $data['type'],
            'entity_type' => $data['entity_type'],
            'lookup_type' => $data['lookup_type'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'validation' => $data['validation'] ?? null,
            'is_required' => $data['is_required'] ?? false,
            'is_unique' => $data['is_unique'] ?? false,
            'quick_add' => $data['quick_add'] ?? false,
            'is_user_defined' => true,
        ]);

        $this->syncOptions($attribute, $data['options'] ?? []);

        return response()->json($attribute->load('options'), 201);
    }

    public function updateField(Request $request, Attribute $field): JsonResponse
    {
        abort_unless($field->is_user_defined, 404);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:160'],
            'type' => ['sometimes', 'in:text,textarea,price,boolean,select,multiselect,checkbox,email,address,phone,lookup,datetime,date,file,image'],
            'lookup_type' => ['nullable', 'string', 'max:80'],
            'validation' => ['nullable', 'string', 'max:255'],
            'is_required' => ['sometimes', 'boolean'],
            'is_unique' => ['sometimes', 'boolean'],
            'quick_add' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'options' => ['sometimes', 'array'],
            'options.*.name' => ['required_with:options', 'string', 'max:160'],
            'options.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $options = $data['options'] ?? null;
        unset($data['options']);
        $field->update($data);

        if ($options !== null) {
            $this->syncOptions($field, $options);
        }

        return response()->json($field->fresh()->load('options'));
    }

    public function deleteField(Request $request, Attribute $field): JsonResponse
    {
        abort_unless($field->is_user_defined, 404);
        $field->delete();

        return response()->json(['message' => 'Attribute deleted.']);
    }

    private function syncOptions(Attribute $attribute, array $options): void
    {
        $attribute->options()->delete();

        foreach (array_values($options) as $index => $option) {
            AttributeOption::create([
                'attribute_id' => $attribute->id,
                'name' => $option['name'],
                'sort_order' => $option['sort_order'] ?? $index,
            ]);
        }
    }
}
