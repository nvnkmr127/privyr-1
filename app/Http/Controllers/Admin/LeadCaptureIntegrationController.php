<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\LeadSourceCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Webkul\Lead\Models\LeadSourceConnector;
use Webkul\Lead\Repositories\PipelineRepository;
use Webkul\Lead\Services\LeadCaptureService;
use Webkul\User\Repositories\UserRepository;

class LeadCaptureIntegrationController extends Controller
{
    public function __construct(
        protected PipelineRepository $pipelineRepository,
        protected UserRepository $userRepository,
        protected LeadCaptureService $leadCaptureService
    ) {}

    /**
     * Directory of lead sources + the connectors already created for each.
     */
    public function index(Request $request)
    {
        $connectors = LeadSourceConnector::latest()
            ->get()
            ->groupBy('source_type');

        $integrations = collect(LeadSourceCatalog::all())->map(function ($source) use ($connectors) {
            $existing = $connectors->get($source['source_type'], collect())
                ->map(fn ($c) => $this->connectorPayload($c))
                ->values();

            return array_merge($source, ['connectors' => $existing]);
        })->values();

        return view('admin::lead_capture.integrations', [
            'integrations' => $integrations,
            'pipelines' => $this->pipelineRepository->all(['id', 'name']),
            'users' => $this->userRepository->all(['id', 'name']),
        ]);
    }

    /**
     * Create a real connector for a source and return its live integration details.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'source_type' => ['required', Rule::in(LeadSourceCatalog::sourceTypes())],
            'duplicate_action' => ['nullable', Rule::in(['update', 'attach_contact', 'skip'])],
            'mapping' => 'nullable|array',
            'embed_config' => 'nullable|array',
            'pipeline' => ['nullable', Rule::exists('lead_pipelines', 'id')],
            'assignee' => ['nullable', Rule::exists('users', 'id')],
        ]);

        $connector = LeadSourceConnector::create([
            'name' => $validated['name'],
            'source_type' => $validated['source_type'],
            'webhook_token' => Str::random(32),
            'api_key' => Str::random(40),
            'duplicate_action' => $validated['duplicate_action'] ?? 'update',
            'field_mappings' => array_filter($request->input('mapping', [])),
            'embed_config' => $request->input('embed_config'),
            'default_lead_pipeline_id' => $validated['pipeline'] ?? null,
            'default_user_id' => $validated['assignee'] ?? null,
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Integration created.',
            'connector' => $this->connectorPayload($connector),
        ]);
    }

    /**
     * Run a real dry-run test lead through the connector's mapping / dedup /
     * routing pipeline. Creates no Person, Lead, or log — verifies the flow.
     */
    public function test(Request $request, int $id)
    {
        $connector = LeadSourceConnector::whereKey($id)
            ->firstOrFail();

        $sample = $request->input('payload', [
            'name' => 'Test Lead',
            'email' => 'test.lead@example.com',
            'phone' => '+1 555 010 0000',
            'message' => 'Sample payload from the integration tester.',
        ]);

        try {
            $preview = $this->leadCaptureService->processIncomingPayload($connector, $sample, null, null, true);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }

        return response()->json(['status' => 'ok', 'preview' => $preview]);
    }

    /**
     * Disconnect (deactivate) a connector (tenant-scoped).
     */
    public function disconnect(Request $request, int $id)
    {
        $connector = LeadSourceConnector::whereKey($id)
            ->firstOrFail();
        $connector->update(['is_active' => false]);

        return response()->json(['message' => 'Integration disconnected.', 'connector' => $this->connectorPayload($connector)]);
    }

    /**
     * Re-enable a previously disconnected connector (tenant-scoped).
     */
    public function reconnect(Request $request, int $id)
    {
        $connector = LeadSourceConnector::whereKey($id)
            ->firstOrFail();
        $connector->update(['is_active' => true]);

        return response()->json(['message' => 'Integration re-enabled.', 'connector' => $this->connectorPayload($connector)]);
    }

    /**
     * Serialise a connector for the browser. Deliberately excludes secrets
     * (api_key, meta_page_access_token are $hidden on the model) — only public
     * identifiers and the token URL reach the client.
     */
    protected function connectorPayload(LeadSourceConnector $connector): array
    {
        return [
            'id' => $connector->id,
            'name' => $connector->name,
            'token' => $connector->webhook_token,
            'source_type' => $connector->source_type,
            'is_active' => (bool) $connector->is_active,
            'captured_count' => $connector->captured_count,
            'last_received_at' => optional($connector->last_received_at)->diffForHumans(),
            'duplicate_action' => $connector->duplicate_action,
            'field_mappings' => $connector->field_mappings ?? [],
            'embed_config' => $connector->embed_config,
            'default_lead_pipeline_id' => $connector->default_lead_pipeline_id,
            'default_user_id' => $connector->default_user_id,
            'webhook_url' => route('api.v1.lead_capture.webhook', ['token' => $connector->webhook_token]),
            'form_url' => route('public.lead_capture.qr_form', ['token' => $connector->webhook_token]),
            'embed_js_url' => route('public.lead_capture.embed_js', ['token' => $connector->webhook_token]),
            'connection' => $this->connectionStatus($connector),
        ];
    }

    /**
     * Truthful connection status. Meta connectors are only "connected" when a
     * Page token is actually stored; otherwise they need OAuth (which itself
     * requires FACEBOOK_CLIENT_ID / SECRET to be configured on the server).
     */
    protected function connectionStatus(LeadSourceConnector $connector): array
    {
        if ($connector->source_type !== 'meta_ads') {
            return ['state' => $connector->is_active ? 'active' : 'disconnected'];
        }

        $configured = (bool) config('services.facebook.client_id') && (bool) config('services.facebook.client_secret');

        if ($connector->meta_page_id) {
            return [
                'state' => 'connected',
                'page' => $connector->meta_page_name ?? $connector->meta_page_id,
                'connect_url' => route('admin.settings.lead_connectors.facebook.connect', $connector->id),
            ];
        }

        return [
            'state' => $configured ? 'needs_connect' : 'unconfigured',
            'connect_url' => $configured ? route('admin.settings.lead_connectors.facebook.connect', $connector->id) : null,
            'message' => $configured ? null : 'Set FACEBOOK_CLIENT_ID and FACEBOOK_CLIENT_SECRET on the server to enable Page connection.',
        ];
    }
}
