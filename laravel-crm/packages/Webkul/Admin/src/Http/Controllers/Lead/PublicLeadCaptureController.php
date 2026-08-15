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
     * Handle IndiaMART API Lead Push.
     */
    public function handleIndiaMART(Request $request): JsonResponse
    {
        $connector = LeadSourceConnector::where('source_type', 'indiamart')
            ->where('is_active', true)
            ->first();

        if (! $connector) {
            return response()->json(['status' => 'error', 'message' => 'IndiaMART connector not configured'], 404);
        }

        $payload = $request->all();
        $lead = $this->leadCaptureService->processIncomingPayload($connector, $payload);

        return response()->json([
            'RESPONSE' => 'SUCCESS',
            'CODE' => 200,
            'MESSAGE' => 'IndiaMART lead processed successfully',
            'LEAD_ID' => $lead?->id,
        ]);
    }

    /**
     * Handle JustDial API Lead Push.
     */
    public function handleJustDial(Request $request): JsonResponse
    {
        $connector = LeadSourceConnector::where('source_type', 'justdial')
            ->where('is_active', true)
            ->first();

        if (! $connector) {
            return response()->json(['status' => 'error', 'message' => 'JustDial connector not configured'], 404);
        }

        $payload = $request->all();
        $lead = $this->leadCaptureService->processIncomingPayload($connector, $payload);

        return response()->json([
            'status' => 'success',
            'message' => 'JustDial lead processed',
            'lead_id' => $lead?->id,
        ]);
    }

    /**
     * Handle Real Estate Portal Lead Push (99acres, MagicBricks, Housing, Sulekha).
     */
    public function handleRealEstate(Request $request): JsonResponse
    {
        $sourceType = $request->input('source_type', 'realestate_99acres');

        $connector = LeadSourceConnector::where('source_type', $sourceType)
            ->where('is_active', true)
            ->first() ?? LeadSourceConnector::where('source_type', 'webhook')->first();

        if (! $connector) {
            return response()->json(['status' => 'error', 'message' => 'Real estate portal connector not configured'], 404);
        }

        $payload = $request->all();
        $lead = $this->leadCaptureService->processIncomingPayload($connector, $payload);

        return response()->json([
            'status' => 'success',
            'message' => 'Portal lead processed',
            'lead_id' => $lead?->id,
        ]);
    }

    /**
     * Show QR Code Public Lead Capture Form.
     */
    public function qrForm(string $token)
    {
        $connector = LeadSourceConnector::where('webhook_token', $token)
            ->where('is_active', true)
            ->firstOrFail();

        return view('admin::leads.qr-capture', [
            'connector' => $connector,
        ]);
    }

    /**
     * Store Public QR Code Lead Form Submission.
     */
    public function qrStore(Request $request, string $token)
    {
        $connector = LeadSourceConnector::where('webhook_token', $token)
            ->where('is_active', true)
            ->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'required|string|max:50',
        ]);

        $payload = $request->all();
        $this->leadCaptureService->processIncomingPayload($connector, $payload);

        return view('admin::leads.qr-capture-success', [
            'connector' => $connector,
        ]);
    }
}
