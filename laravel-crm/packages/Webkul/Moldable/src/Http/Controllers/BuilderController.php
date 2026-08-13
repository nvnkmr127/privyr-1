<?php

namespace Webkul\Moldable\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Webkul\Attribute\Models\Attribute;
use Webkul\Attribute\Models\AttributeOption;
use Webkul\Moldable\Services\FieldTypeRegistry;

class BuilderController
{
    public function reorderFields(Request $request): JsonResponse
    {
        $data = $request->validate([
            'orders' => ['required', 'array'],
            'orders.*.id' => ['required', 'integer', 'exists:attributes,id'],
            'orders.*.sort_order' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($data['orders'] as $item) {
            Attribute::whereKey($item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['message' => 'Fields reordered successfully.']);
    }

    public function entities(): JsonResponse
    {
        $entities = config('moldable.entities', []);
        return response()->json(array_values($entities));
    }

    public function types(): JsonResponse
    {
        return response()->json(FieldTypeRegistry::all());
    }

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
            'type' => ['required', FieldTypeRegistry::validationRule()],
            'lookup_type' => ['nullable', 'string', 'max:80'],
            'validation' => ['nullable', 'string', 'max:255'],
            'is_required' => ['nullable', 'boolean'],
            'is_unique' => ['nullable', 'boolean'],
            'quick_add' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'options' => ['nullable', 'array'],
            'options.*.id' => ['nullable', 'integer', 'exists:attribute_options,id'],
            'options.*.name' => ['required_with:options', 'string', 'max:160'],
            'options.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        if (Attribute::where('entity_type', $data['entity_type'])->whereRaw('LOWER(name) = ?', [mb_strtolower($data['name'])])->exists()) {
            return response()->json(['message' => 'An attribute with this display name already exists for this entity.'], 422);
        }

        $code = $this->generateUniqueCode($data['name'], $data['entity_type'], $data['code'] ?? null);

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
        if (! $field->is_user_defined) {
            return response()->json(['message' => 'System attributes cannot be modified.'], 403);
        }

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:160'],
            'type' => ['sometimes', FieldTypeRegistry::validationRule()],
            'lookup_type' => ['nullable', 'string', 'max:80'],
            'validation' => ['nullable', 'string', 'max:255'],
            'is_required' => ['sometimes', 'boolean'],
            'is_unique' => ['sometimes', 'boolean'],
            'quick_add' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'options' => ['sometimes', 'array'],
            'options.*.id' => ['nullable', 'integer', 'exists:attribute_options,id'],
            'options.*.name' => ['required_with:options', 'string', 'max:160'],
            'options.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        unset($data['code'], $data['entity_type'], $data['is_user_defined']);

        if (isset($data['name'])) {
            $duplicateNameExists = Attribute::where('entity_type', $field->entity_type)
                ->where('id', '!=', $field->id)
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($data['name'])])
                ->exists();

            if ($duplicateNameExists) {
                return response()->json(['message' => 'An attribute with this display name already exists for this entity.'], 422);
            }
        }

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
        if (! $field->is_user_defined) {
            return response()->json(['message' => 'System attributes cannot be deleted.'], 403);
        }

        $field->delete();

        return response()->json(['message' => 'Attribute deleted.']);
    }

    private function syncOptions(Attribute $attribute, array $options): void
    {
        $existingOptionIds = [];

        foreach (array_values($options) as $index => $optionData) {
            $name = is_array($optionData) ? ($optionData['name'] ?? null) : $optionData;
            if (empty($name)) {
                continue;
            }

            $sortOrder = is_array($optionData) ? ($optionData['sort_order'] ?? $index) : $index;
            $optionId = is_array($optionData) ? ($optionData['id'] ?? null) : null;

            if ($optionId) {
                $option = $attribute->options()->find($optionId);
                if ($option) {
                    $option->update([
                        'name' => $name,
                        'sort_order' => $sortOrder,
                    ]);
                    $existingOptionIds[] = $option->id;
                    continue;
                }
            }

            $newOption = AttributeOption::create([
                'attribute_id' => $attribute->id,
                'name' => $name,
                'sort_order' => $sortOrder,
            ]);
            $existingOptionIds[] = $newOption->id;
        }

        $attribute->options()->whereNotIn('id', $existingOptionIds)->delete();
    }

    private function generateUniqueCode(string $name, string $entityType, ?string $customCode = null): string
    {
        $base = ! empty($customCode) ? $customCode : $name;
        $slug = Str::limit(Str::snake($base), 110, '');
        if (empty($slug)) {
            $slug = 'attr';
        }

        $code = $slug;
        $counter = 2;

        while (Attribute::where('code', $code)->where('entity_type', $entityType)->exists()) {
            $code = $slug.'_'.$counter;
            $counter++;
        }

        return $code;
    }
}
