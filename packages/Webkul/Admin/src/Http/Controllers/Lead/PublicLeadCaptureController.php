<?php

namespace Webkul\Admin\Http\Controllers\Lead;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Lead\Models\LeadSourceConnector;
use Webkul\Lead\Services\LeadCaptureService;

class PublicLeadCaptureController extends Controller
{
    public function __construct(
        protected LeadCaptureService $leadCaptureService
    ) {}

    /**
     * Handle incoming webhooks (Meta Ads, Google Ads, Zapier, Make, Generic Webhooks, Custom REST API).
     */
    public function handleWebhook(Request $request, string $token): JsonResponse
    {
        $connector = LeadSourceConnector::where('webhook_token', $token)
            ->where('is_active', true)
            ->first();

        if (! $connector) {
            return response()->json(['status' => 'error', 'message' => 'Invalid or inactive webhook token'], 404);
        }

        // Meta Lead Ads subscription verification challenge (GET). Echo the
        // challenge only when the verify token matches (when one is configured).
        if ($request->has('hub_challenge')) {
            $expected = config('services.facebook.webhook_verify_token');
            $provided = $request->input('hub_verify_token');

            if ($expected && $provided !== null && ! hash_equals((string) $expected, (string) $provided)) {
                return response()->json(['status' => 'error', 'message' => 'Verification failed'], 403);
            }

            return response((string) $request->input('hub_challenge'), 200);
        }

        try {
            $lead = $this->leadCaptureService->processIncomingPayload(
                $connector,
                $request->all(),
                $request->getContent(),
                $request->header('X-Hub-Signature-256'),
            );
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Lead captured successfully',
            'lead_id' => $lead?->id,
        ], 201);
    }

    /**
     * Handle IndiaMART API Lead Push. Tenant-safe: requires the connector's
     * webhook token (?token= or `token` in body) so the lead is owned by the
     * correct tenant — never resolved by source_type across all tenants.
     */
    public function handleIndiaMART(Request $request): JsonResponse
    {
        $connector = $this->resolveConnectorByToken($request, 'indiamart');

        if (! $connector) {
            return response()->json(['RESPONSE' => 'ERROR', 'CODE' => 404, 'MESSAGE' => 'Invalid or missing connector token. Use your connector\'s webhook URL.'], 404);
        }

        $lead = $this->leadCaptureService->processIncomingPayload($connector, $request->all());

        return response()->json([
            'RESPONSE' => 'SUCCESS',
            'CODE' => 200,
            'MESSAGE' => 'IndiaMART lead processed successfully',
            'LEAD_ID' => $lead?->id,
        ]);
    }

    /**
     * Handle JustDial API Lead Push. Tenant-safe (token required).
     */
    public function handleJustDial(Request $request): JsonResponse
    {
        $connector = $this->resolveConnectorByToken($request, 'justdial');

        if (! $connector) {
            return response()->json(['status' => 'error', 'message' => 'Invalid or missing connector token. Use your connector\'s webhook URL.'], 404);
        }

        $lead = $this->leadCaptureService->processIncomingPayload($connector, $request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'JustDial lead processed',
            'lead_id' => $lead?->id,
        ]);
    }

    /**
     * Handle Real Estate Portal Lead Push (99acres, MagicBricks, Housing,
     * Sulekha). Tenant-safe (token required).
     */
    public function handleRealEstate(Request $request): JsonResponse
    {
        $connector = $this->resolveConnectorByToken($request);

        if (! $connector) {
            return response()->json(['status' => 'error', 'message' => 'Invalid or missing connector token. Use your connector\'s webhook URL.'], 404);
        }

        $lead = $this->leadCaptureService->processIncomingPayload($connector, $request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Portal lead processed',
            'lead_id' => $lead?->id,
        ]);
    }

    /**
     * Resolve an active connector from the request's webhook token. The token
     * is globally unique, so it maps to exactly one connector and therefore one
     * tenant — no cross-tenant ambiguity. Optionally constrain to a source_type.
     */
    protected function resolveConnectorByToken(Request $request, ?string $sourceType = null): ?LeadSourceConnector
    {
        $token = $request->input('token') ?? $request->query('token');

        if (! $token) {
            return null;
        }

        $query = LeadSourceConnector::where('webhook_token', $token)->where('is_active', true);

        if ($sourceType) {
            $query->where('source_type', $sourceType);
        }

        return $query->first();
    }

    /**
     * Show the hosted lead capture form (also used as the QR target and the
     * iframe embed source). Honors the connector's embed_config.
     */
    public function qrForm(Request $request, string $token)
    {
        $connector = LeadSourceConnector::where('webhook_token', $token)
            ->where('is_active', true)
            ->firstOrFail();

        return view('admin::leads.qr-capture', [
            'connector' => $connector,
            'config' => $this->embedConfig($connector),
            'embedded' => $request->boolean('embed'),
        ]);
    }

    /**
     * Store a hosted-form submission, then either redirect (configured
     * thank-you URL) or show the success page.
     */
    public function qrStore(Request $request, string $token)
    {
        $connector = LeadSourceConnector::where('webhook_token', $token)
            ->where('is_active', true)
            ->firstOrFail();

        $config = $this->embedConfig($connector);

        $rules = [
            'name' => 'required|string|max:255',
            'email' => ($config['fields']['email'] ?? true) ? 'required|email' : 'nullable|email',
            'phone' => ($config['fields']['phone'] ?? true) ? 'required|string|max:50' : 'nullable|string|max:50',
        ];

        $request->validate($rules);

        $this->leadCaptureService->processIncomingPayload($connector, $request->all());

        if (! empty($config['redirect_url'])) {
            return redirect()->away($config['redirect_url']);
        }

        return view('admin::leads.qr-capture-success', [
            'connector' => $connector,
            'config' => $config,
            'embedded' => $request->boolean('embed'),
        ]);
    }

    /**
     * JavaScript loader for the <script>+<div data-lead-capture> embed. Injects
     * the hosted form as a responsive iframe and auto-resizes it via postMessage.
     * Returns an inert script for unknown/inactive tokens (no information leak).
     */
    public function embedJs(string $token)
    {
        $connector = LeadSourceConnector::where('webhook_token', $token)
            ->where('is_active', true)
            ->first();

        $headers = [
            'Content-Type' => 'application/javascript',
            'Cache-Control' => 'public, max-age=300',
        ];

        if (! $connector) {
            return response("/* lead-capture: unknown or inactive token */", 200, $headers);
        }

        $src = route('public.lead_capture.qr_form', ['token' => $token]).'?embed=1';

        $js = <<<JS
(function () {
    var SRC = {$this->jsString($src)};
    var TOKEN = {$this->jsString($token)};
    function mount(el) {
        if (el.getAttribute('data-lc-mounted')) return;
        el.setAttribute('data-lc-mounted', '1');
        var f = document.createElement('iframe');
        f.src = SRC;
        f.setAttribute('title', 'Lead capture form');
        f.style.width = '100%';
        f.style.border = '0';
        f.style.minHeight = '520px';
        f.setAttribute('scrolling', 'no');
        el.appendChild(f);
        window.addEventListener('message', function (e) {
            if (!e.data || e.data.lcToken !== TOKEN) return;
            if (e.data.lcHeight) f.style.height = e.data.lcHeight + 'px';
        });
    }
    function boot() {
        var nodes = document.querySelectorAll('[data-lead-capture="' + TOKEN + '"]');
        for (var i = 0; i < nodes.length; i++) mount(nodes[i]);
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
JS;

        return response($js, 200, $headers);
    }

    /**
     * Merge stored embed_config over defaults so the hosted form always has a
     * complete, safe config even for connectors created before embed support.
     */
    protected function embedConfig(LeadSourceConnector $connector): array
    {
        $stored = $connector->embed_config ?? [];

        return [
            'title' => $stored['title'] ?? $connector->name,
            'subtitle' => $stored['subtitle'] ?? 'Please enter your details below and we\'ll get in touch.',
            'button_text' => $stored['button_text'] ?? 'Submit',
            'redirect_url' => $stored['redirect_url'] ?? null,
            'fields' => [
                'email' => $stored['fields']['email'] ?? true,
                'phone' => $stored['fields']['phone'] ?? true,
                'message' => $stored['fields']['message'] ?? true,
            ],
        ];
    }

    /**
     * Encode a value as a safe JS string literal for inlining into the loader.
     */
    protected function jsString(string $value): string
    {
        return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    }
}
