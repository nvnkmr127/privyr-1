<?php

namespace Webkul\API\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Webkul\API\Http\Requests\LeadIngestionRequest;
use Webkul\Lead\Contracts\LeadIngestionService;
use Webkul\Lead\DataTransferObjects\LeadIngestionPayload;
use Webkul\Lead\Exceptions\LeadIngestionException;

class LeadIngestionController extends Controller
{
    protected $leadIngestionService;

    public function __construct(LeadIngestionService $leadIngestionService)
    {
        $this->leadIngestionService = $leadIngestionService;
    }

    /**
     * Store a newly created lead in storage.
     *
     * @param LeadIngestionRequest $request
     * @return JsonResponse
     */
    public function store(LeadIngestionRequest $request): JsonResponse
    {
        $idempotencyKey = $request->header('Idempotency-Key');
        
        if ($idempotencyKey) {
            $cacheKey = 'idempotency_lead_' . $idempotencyKey;
            if (Cache::has($cacheKey)) {
                $cachedResponse = Cache::get($cacheKey);
                return response()->json($cachedResponse, 200);
            }
        }

        $payload = new LeadIngestionPayload(
            connectorId: 'api', // Should ideally be unique per credential
            origin: $request->input('origin'),
            sourceId: null,
            sourceName: $request->input('source'),
            externalId: $request->input('external_id') ?? $request->input('external_source') . '_' . $request->input('external_id'),
            leadData: [
                'emails' => $request->input('email') ? [['value' => $request->input('email'), 'label' => 'work']] : [],
                'contact_numbers' => $request->input('phone') ? [['value' => $request->input('phone'), 'label' => 'work']] : [],
                'person' => [
                    'name' => $request->input('name') ?? 'Unknown',
                ],
                'title' => $request->input('name') ? 'Lead - ' . $request->input('name') : 'Lead from API',
                'lead_pipeline_id' => $request->input('pipeline'),
                'lead_pipeline_stage_id' => $request->input('stage'),
                'user_id' => $request->input('owner'),
            ],
            metadata: array_merge($request->input('metadata') ?? [], [
                'campaign' => $request->input('campaign'),
                'medium' => $request->input('medium'),
                'content' => $request->input('content'),
                'term' => $request->input('term'),
                'landing_page' => $request->input('landing_page'),
                'form_id' => $request->input('form'),
            ]),
            duplicateAction: 'skip'
        );

        try {
            $lead = $this->leadIngestionService->ingest($payload);

            $response = [
                'success' => true,
                'lead_id' => $lead->id,
                'external_id' => $payload->externalId,
                'source' => $request->input('source'),
                'status' => 'created'
            ];

            if ($idempotencyKey) {
                $cacheKey = 'idempotency_lead_' . $idempotencyKey;
                Cache::put($cacheKey, $response, now()->addHours(24));
            }

            return response()->json($response, 201);
            
        } catch (LeadIngestionException $e) {
            if ($e->getCode() === 409) {
                return response()->json([
                    'success' => false,
                    'message' => 'Duplicate lead',
                    'match_type' => 'duplicate',
                ], 409);
            }
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            Log::error('API Lead Ingestion Failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Internal Server Error'
            ], 500);
        }
    }
}
