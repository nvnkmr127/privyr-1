<?php

namespace Webkul\Moldable\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Webkul\Attribute\Models\Attribute;
use Webkul\Attribute\Models\AttributeOption;
use Webkul\Moldable\Models\IndustryTemplate;
use Webkul\Moldable\Models\SavedView;
use Webkul\Moldable\Services\FieldTypeRegistry;

class TemplateController
{
    public function index(): JsonResponse
    {
        return response()->json(IndustryTemplate::query()->where('is_active', true)->orderBy('industry')->orderBy('name')->get());
    }

    public function install(Request $request, IndustryTemplate $template): JsonResponse
    {
        $workspace = $request->attributes->get('moldable_workspace');
        abort_unless($template->is_active, 404);

        $definition = $template->definition ?? [];
        $createdFields = [];

        foreach ($definition['fields'] ?? [] as $index => $field) {
            if (empty($field['key']) || empty($field['label']) || empty($field['type']) || ! FieldTypeRegistry::isValid($field['type'])) {
                continue;
            }

            $attribute = Attribute::firstOrCreate(
                ['code' => $field['key'], 'entity_type' => 'leads'],
                [
                    'name' => $field['label'],
                    'type' => $field['type'],
                    'sort_order' => $index,
                    'validation' => $field['validation'] ?? null,
                    'is_required' => $field['is_required'] ?? false,
                    'is_unique' => false,
                    'quick_add' => $field['quick_add'] ?? false,
                    'is_user_defined' => true,
                ]
            );

            $attribute->update([
                'name' => $field['label'],
                'type' => $field['type'],
                'sort_order' => $index,
                'is_user_defined' => true,
            ]);

            if (! empty($field['options'])) {
                $attribute->options()->delete();
                foreach (array_values($field['options']) as $optionIndex => $option) {
                    AttributeOption::create([
                        'attribute_id' => $attribute->id,
                        'name' => is_array($option) ? $option['name'] : $option,
                        'sort_order' => is_array($option) ? ($option['sort_order'] ?? $optionIndex) : $optionIndex,
                    ]);
                }
            }

            $createdFields[] = $attribute->load('options');
        }

        $createdViews = [];
        foreach ($definition['views'] ?? [] as $view) {
            if (empty($view['name'])) {
                continue;
            }

            $createdViews[] = SavedView::updateOrCreate(
                ['workspace_id' => $workspace->id, 'entity_type' => 'leads', 'name' => $view['name']],
                [
                    'user_id' => $request->user()->getAuthIdentifier(),
                    'filters' => $view['filters'] ?? [],
                    'columns' => $view['columns'] ?? [],
                    'sort' => $view['sort'] ?? [],
                    'group_by' => $view['group_by'] ?? [],
                    'visibility' => 'workspace',
                    'is_default' => false,
                ]
            );
        }

        return response()->json([
            'message' => 'Industry template installed using the existing Attribute system.',
            'template' => $template->key,
            'fields' => $createdFields,
            'views' => $createdViews,
        ]);
    }
}
