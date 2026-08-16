<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MessageTemplate;
use App\Services\MessageTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContentTemplateController extends Controller
{
    public function __construct(
        protected MessageTemplateService $templateService
    ) {}

    /**
     * List saved message templates with auto-personalization placeholders.
     */
    public function index()
    {
        $templates = MessageTemplate::where('is_active', true)
            ->orderBy('id')
            ->get(['id', 'name', 'category', 'content']);

        return response()->json([
            'status' => 'success',
            'placeholders' => ['{name}', '{title}', '{phone}', '{email}', '{value}', '{source}', '{agent_name}'],
            'templates' => $templates,
        ]);
    }

    /**
     * Create a new template in the content library.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'content' => 'required|string',
        ]);

        $template = MessageTemplate::create($data + ['is_active' => true]);

        return response()->json(['status' => 'success', 'template' => $template], 201);
    }

    /**
     * Update an existing template.
     */
    public function update(Request $request, $id)
    {
        $template = MessageTemplate::find($id);

        if (! $template) {
            return response()->json(['status' => 'error', 'message' => 'Template not found.'], 404);
        }

        $template->update($request->validate([
            'name' => 'sometimes|required|string|max:255',
            'category' => 'nullable|string|max:100',
            'content' => 'sometimes|required|string',
            'is_active' => 'sometimes|boolean',
        ]));

        return response()->json(['status' => 'success', 'template' => $template]);
    }

    /**
     * Delete a template from the content library.
     */
    public function destroy($id)
    {
        MessageTemplate::where('id', $id)->delete();

        return response()->json(['status' => 'success', 'message' => 'Template deleted.']);
    }

    /**
     * Preview personalized template for a specific lead.
     */
    public function preview(Request $request, $lead)
    {
        $leadRecord = is_object($lead) ? $lead : DB::table('leads')->where('id', $lead)->first();

        if (! $leadRecord) {
            return response()->json(['status' => 'error', 'message' => 'Lead not found.'], 404);
        }

        $templateText = $request->input('template', 'Hi {name}, thanks for reaching out regarding {title}!');
        $personalized = $this->templateService->parse($templateText, $leadRecord);

        return response()->json([
            'status' => 'success',
            'original' => $templateText,
            'personalized' => $personalized,
        ]);
    }
}
