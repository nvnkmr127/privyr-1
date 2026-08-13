<?php

namespace Webkul\Moldable\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Webkul\Moldable\Models\CustomField;
use Webkul\Moldable\Models\IndustryTemplate;
use Webkul\Moldable\Models\SavedView;

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
            if (empty($field['key']) || empty($field['label']) || empty($field['type'])) {
                continue;
            }

            $createdFields[] = CustomField::updateOrCreate(
                ['workspace_id' => $workspace->id, 'entity_type' => 'lead', 'key' => $field['key']],
                [
                    'label' => $field['label'],
                    'type' => $field['type'],
                    'options' => $field['options'] ?? [],
                    'config' => $field['config'] ?? [],
                    'group_name' => $field['group_name'] ?? $template->industry,
                    'sort_order' => $index,
                    'is_required' => $field['is_required'] ?? false,
                    'is_active' => true,
                ]
            );
        }

        $createdViews = [];
        foreach ($definition['views'] ?? [] as $view) {
            if (empty($view['name'])) {
                continue;
            }

            $createdViews[] = SavedView::updateOrCreate(
                ['workspace_id' => $workspace->id, 'entity_type' => 'lead', 'name' => $view['name']],
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
            'message' => 'Industry template installed.',
            'template' => $template->key,
            'fields' => $createdFields,
            'views' => $createdViews,
        ]);
    }
}
