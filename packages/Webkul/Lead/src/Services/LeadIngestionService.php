<?php

namespace Webkul\Lead\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Webkul\Lead\Contracts\Lead;
use Webkul\Lead\Contracts\LeadIngestionService as LeadIngestionServiceContract;
use Webkul\Lead\DataTransferObjects\LeadIngestionPayload;
use Webkul\Lead\Exceptions\LeadIngestionException;
use Webkul\Lead\Models\LeadCaptureLog;
use Webkul\Lead\Repositories\LeadRepository;
use Webkul\Lead\Repositories\PipelineRepository;
use Webkul\Lead\Repositories\SourceRepository;

class LeadIngestionService implements LeadIngestionServiceContract
{
    public function __construct(
        protected LeadRepository $leadRepository,
        protected DuplicateMatchingService $duplicateMatchingService,
        protected SourceRepository $sourceRepository,
        protected PipelineRepository $pipelineRepository,
        protected LeadAttributionService $leadAttributionService,
        protected LeadDataQualityService $leadDataQualityService
    ) {}

    /**
     * Ingest a lead from any origin.
     *
     * @return Lead
     *
     * @throws LeadIngestionException
     */
    public function ingest(LeadIngestionPayload $payload)
    {
        $startTime = microtime(true);
        $logId = null;

        try {
            DB::beginTransaction();

            // 1. Log ingestion attempt
            $log = LeadCaptureLog::create([
                'connector_id' => $payload->connectorId,
                'origin' => $payload->origin,
                'external_id' => $payload->externalId,
                'raw_payload' => $payload->leadData,
                'status' => 'Received',
            ]);
            $logId = $log->id;

            Event::dispatch('lead.ingestion.received', $payload);

            // 2. Normalize and Validate
            $leadData = $this->normalizeData($payload);

            // Centralized Data Quality Normalization
            $leadData = $this->leadDataQualityService->normalize($leadData);

            // Centralized Validation
            $validationErrors = $this->leadDataQualityService->validate($leadData);
            if (! empty($validationErrors)) {
                $this->updateLog($logId, 'Validation Failed', null, $startTime, json_encode($validationErrors));
                throw new LeadIngestionException('Validation Failed: '.json_encode($validationErrors));
            }

            Event::dispatch('lead.ingestion.validated', $leadData);

            // 3. Duplicate Check
            $duplicate = $this->duplicateMatchingService->findDuplicate(
                $leadData['emails'] ?? [],
                $leadData['contact_numbers'] ?? [],
                $payload->externalId,
                $payload->origin
            );

            $leadData['entity_type'] = 'leads';

            if ($duplicate) {
                if ($payload->duplicateAction === 'skip') {
                    $this->updateLog($logId, 'Duplicate Skipped', $duplicate->id, $startTime, 'Duplicate lead detected and skipped.');
                    Event::dispatch('lead.ingestion.duplicate', $duplicate);
                    DB::commit();

                    return $duplicate; // Or return null depending on convention, but returning the duplicate is fine.
                }

                if ($payload->duplicateAction === 'update') {
                    // Update existing lead instead of creating a new one
                    $leadData = $this->leadAttributionService->mapAttributionData($leadData, true);

                    Event::dispatch('lead.update.before', $duplicate->id);
                    $lead = $this->leadRepository->update($leadData, $duplicate->id);

                    $this->leadAttributionService->recordHistory($lead, $leadData);

                    Event::dispatch('lead.update.after', $lead);

                    $this->updateLog($logId, 'Updated', $lead->id, $startTime);
                    Event::dispatch('lead.ingestion.updated', $lead);
                    DB::commit();

                    return $lead;
                }

                // Default 'reject' or idempotent match
                if ($payload->externalId && $payload->origin && $duplicate->external_id === $payload->externalId && $duplicate->origin === $payload->origin) {
                    $this->updateLog($logId, 'Duplicate', $duplicate->id, $startTime);
                    Event::dispatch('lead.ingestion.duplicate', $duplicate);
                    DB::commit();

                    return $duplicate;
                }

                // Configurable duplicate action can be added here
                // For now, we reject duplicates to prevent overwrite
                $this->updateLog($logId, 'Rejected', null, $startTime, 'Duplicate lead detected.');
                Event::dispatch('lead.ingestion.rejected', $payload);
                DB::commit();

                // Throw exception outside the transaction so it doesn't trigger rollback
                throw LeadIngestionException::duplicate();
            }

            // 4. Create Lead
            Event::dispatch('lead.create.before');

            $leadData = $this->leadAttributionService->mapAttributionData($leadData, false);
            $lead = $this->leadRepository->create($leadData);

            $this->leadAttributionService->recordHistory($lead, $leadData);

            Event::dispatch('lead.create.after', $lead);

            // 5. Success
            $this->updateLog($logId, 'Created', $lead->id, $startTime);
            Event::dispatch('lead.ingestion.created', $lead);

            DB::commit();

            return $lead;
        } catch (LeadIngestionException $e) {
            // Already handled and logged (e.g., Duplicate), just rethrow
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            if ($logId) {
                $this->updateLog($logId, 'Failed', null, $startTime, $e->getMessage());
            }
            Event::dispatch('lead.ingestion.failed', ['payload' => $payload, 'exception' => $e]);
            throw $e;
        }
    }

    protected function normalizeData(LeadIngestionPayload $payload): array
    {
        $data = $payload->leadData;

        // Ensure title
        if (empty($data['title'])) {
            $data['title'] = 'Lead from '.ucfirst($payload->origin);
        }

        // Apply metadata to lead attributes
        $data['origin'] = $payload->origin;
        $data['external_id'] = $payload->externalId;
        $data['ingestion_status'] = 'Created';

        if (isset($payload->metadata['campaign'])) {
            $data['campaign'] = $payload->metadata['campaign'];
        }

        // Custom EAV fields
        $eavFields = ['medium', 'content', 'term', 'landing_page', 'form_id'];
        foreach ($eavFields as $field) {
            if (isset($payload->metadata[$field])) {
                $data[$field] = $payload->metadata[$field];
            }
        }

        // Set Source Configuration Defaults
        $source = null;
        if ($payload->sourceId) {
            $source = $this->sourceRepository->find($payload->sourceId);
        } elseif ($payload->sourceName) {
            $source = $this->sourceRepository->findOneByField('name', $payload->sourceName);
        }

        if ($source) {
            $data['lead_source_id'] = $source->id;

            if (empty($data['lead_pipeline_id']) && $source->default_lead_pipeline_id) {
                $data['lead_pipeline_id'] = $source->default_lead_pipeline_id;
            }
            if (empty($data['lead_pipeline_stage_id']) && $source->default_lead_pipeline_stage_id) {
                $data['lead_pipeline_stage_id'] = $source->default_lead_pipeline_stage_id;
            }
            if (empty($data['user_id']) && $source->default_user_id) {
                $data['user_id'] = $source->default_user_id;
            }
        } else {
            // Default fallback
            $fallbackSource = $this->sourceRepository->first();
            if ($fallbackSource) {
                $data['lead_source_id'] = $fallbackSource->id;
            }
        }

        // Fallback Pipeline if not set by source or data
        if (empty($data['lead_pipeline_id'])) {
            $pipeline = $this->pipelineRepository->getDefaultPipeline();
            if ($pipeline) {
                $data['lead_pipeline_id'] = $pipeline->id;
                $stage = $pipeline->stages()->first();
                if ($stage && empty($data['lead_pipeline_stage_id'])) {
                    $data['lead_pipeline_stage_id'] = $stage->id;
                }
            }
        }

        return $data;
    }

    protected function updateLog(int $logId, string $status, ?int $leadId, float $startTime, ?string $errorMessage = null)
    {
        $timeMs = round((microtime(true) - $startTime) * 1000);

        LeadCaptureLog::where('id', $logId)->update([
            'status' => $status,
            'lead_id' => $leadId,
            'processing_time_ms' => $timeMs,
            'error_message' => $errorMessage,
        ]);
    }
}
