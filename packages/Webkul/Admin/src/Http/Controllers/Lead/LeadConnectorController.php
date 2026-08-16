<?php

namespace Webkul\Admin\Http\Controllers\Lead;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Lead\Models\LeadSourceConnector;
use Webkul\Lead\Repositories\PipelineRepository;
use Webkul\Lead\Services\LeadCaptureService;
use Webkul\User\Repositories\UserRepository;

class LeadConnectorController extends Controller
{
    public function __construct(
        protected PipelineRepository $pipelineRepository,
        protected UserRepository $userRepository,
        protected LeadCaptureService $leadCaptureService
    ) {}

    /**
     * Display listing of Lead Connectors.
     */
    public function index()
    {
        $connectors = LeadSourceConnector::with(['pipeline', 'stage', 'user'])->latest()->paginate(15);

        return view('admin::settings.lead-connectors.index', [
            'connectors' => $connectors,
            'pipelines' => $this->pipelineRepository->all(),
            'users' => $this->userRepository->all(),
        ]);
    }

    /**
     * Store new Lead Source Connector.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'source_type' => 'required|string',
            'duplicate_action' => 'required|string',
        ]);

        $token = Str::random(32);

        LeadSourceConnector::create([
            'name' => $request->name,
            'source_type' => $request->source_type,
            'webhook_token' => $token,
            'api_key' => Str::random(40),
            'duplicate_action' => $request->duplicate_action,
            'field_mappings' => $request->field_mappings ?? [],
            'default_lead_pipeline_id' => $request->default_lead_pipeline_id,
            'default_lead_pipeline_stage_id' => $request->default_lead_pipeline_stage_id,
            'default_user_id' => $request->default_user_id,
            'is_active' => true,
        ]);

        session()->flash('success', trans('admin::app.settings.lead-connectors.create-success'));

        return redirect()->route('admin.settings.lead_connectors.index');
    }

    /**
     * Update existing Lead Connector and Field Mappings.
     */
    public function update(Request $request, int $id)
    {
        $connector = LeadSourceConnector::findOrFail($id);

        $connector->update([
            'name' => $request->input('name', $connector->name),
            'is_active' => $request->has('is_active') ? (bool) $request->is_active : $connector->is_active,
            'duplicate_action' => $request->input('duplicate_action', $connector->duplicate_action),
            'field_mappings' => $request->input('field_mappings', $connector->field_mappings),
            'default_lead_pipeline_id' => $request->input('default_lead_pipeline_id', $connector->default_lead_pipeline_id),
            'default_lead_pipeline_stage_id' => $request->input('default_lead_pipeline_stage_id', $connector->default_lead_pipeline_stage_id),
            'default_user_id' => $request->input('default_user_id', $connector->default_user_id),
        ]);

        session()->flash('success', trans('admin::app.settings.lead-connectors.update-success'));

        return redirect()->route('admin.settings.lead_connectors.index');
    }

    /**
     * Delete Lead Source Connector.
     */
    public function destroy(int $id)
    {
        $connector = LeadSourceConnector::findOrFail($id);
        $connector->delete();

        session()->flash('success', trans('admin::app.settings.lead-connectors.delete-success'));

        return redirect()->route('admin.settings.lead_connectors.index');
    }

    /**
     * Live AJAX endpoint for Quick Add duplicate detection.
     */
    public function checkDuplicate(Request $request): JsonResponse
    {
        $email = $request->input('email');
        $phone = $request->input('phone');

        // Tenant-scope the check to the current workspace so it never reveals
        // whether a contact exists in another tenant.
        $workspaceId = $request->session()->get('current_workspace_id');

        $duplicate = $this->leadCaptureService->detectDuplicateContact($email, $phone, $workspaceId);

        return response()->json([
            'is_duplicate' => (bool) $duplicate,
            'contact' => $duplicate ? [
                'id' => $duplicate->id,
                'name' => $duplicate->name,
            ] : null,
        ]);
    }

    /**
     * Test Sample Payload field mapping parser.
     */
    public function testMapping(Request $request): JsonResponse
    {
        $payload = $request->input('sample_payload', []);
        $mappings = $request->input('field_mappings', []);

        $mapped = $this->leadCaptureService->mapPayloadToFields($payload, $mappings);

        return response()->json([
            'mapped_result' => $mapped,
        ]);
    }
}
