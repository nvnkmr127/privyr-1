<?php

namespace Webkul\API\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Webkul\Lead\Contracts\LeadIngestionService;
use Webkul\Lead\DataTransferObjects\LeadIngestionPayload;
use Webkul\Lead\Exceptions\LeadIngestionException;

class WebhookIngestionController extends Controller
{
    protected $leadIngestionService;

    public function __construct(LeadIngestionService $leadIngestionService)
    {
        $this->leadIngestionService = $leadIngestionService;
    }

    /**
     * Store a newly created lead from a webhook.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // For webhooks, we usually get a raw payload that needs mapping.
        // Assuming standard payload matching API or mapping logic here.
        
        $payload = new LeadIngestionPayload(
            connectorId: 'webhook',
            origin: $request->input('origin') ?? 'webhook',
            sourceId: null,
            sourceName: $request->input('source'),
            externalId: $request->input('external_id') ?? $request->input('external_source') . '_' . $request->input('external_id'),
            leadData: [
                'emails' => $request->input('email') ? [['value' => $request->input('email'), 'label' => 'work']] : [],
                'contact_numbers' => $request->input('phone') ? [['value' => $request->input('phone'), 'label' => 'work']] : [],
                'person' => [
                    'name' => $request->input('name') ?? 'Unknown Webhook Lead',
                ],
                'title' => $request->input('name') ? 'Lead - ' . $request->input('name') : 'Lead from Webhook',
                'lead_pipeline_id' => $request->input('pipeline'),
                'lead_pipeline_stage_id' => $request->input('stage'),
            ],
            metadata: array_merge($request->input('metadata') ?? [], [
                'campaign' => $request->input('campaign'),
                'medium' => $request->input('medium'),
            ]),
            duplicateAction: 'skip'
        );

        try {
            $lead = $this->leadIngestionService->ingest($payload);

            return response()->json([
                'success' => true,
                'lead_id' => $lead->id,
                'status' => 'created'
            ], 201);
            
        } catch (LeadIngestionException $e) {
            if ($e->getCode() === 409) {
                return response()->json([
                    'success' => false,
                    'message' => 'Duplicate lead',
                ], 409);
            }
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            Log::error('Webhook Lead Ingestion Failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal Server Error'
            ], 500);
        }
    }
}
