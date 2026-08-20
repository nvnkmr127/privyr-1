<?php

namespace Webkul\Lead\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Webkul\DataTransfer\Models\Import;
use Webkul\DataTransfer\Repositories\ImportBatchRepository;
use Webkul\DataTransfer\Repositories\ImportRepository;
use Webkul\Lead\DataTransferObjects\LeadIngestionPayload;
use Webkul\Lead\Exceptions\LeadIngestionException;
use Webkul\Lead\Services\LeadIngestionService;

class LeadIngestionBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 1200; // 20 minutes for large batch

    /**
     * Create a new job instance.
     */
    public function __construct(public int $importId) {}

    /**
     * Execute the job.
     */
    public function handle(
        ImportRepository $importRepository,
        ImportBatchRepository $importBatchRepository,
        LeadIngestionService $ingestionService
    ) {
        $import = $importRepository->find($this->importId);

        if (! $import) {
            return;
        }

        $importRepository->update(['state' => Import::STATE_PROCESSING], $import->id);

        $batches = $importBatchRepository->findWhere([
            'import_id' => $import->id,
            'state' => Import::STATE_PENDING,
        ]);

        $summary = $import->summary ?? ['created' => 0, 'updated' => 0, 'failed' => 0, 'duplicates' => 0];

        foreach ($batches as $batch) {
            $data = is_string($batch->data) ? json_decode($batch->data, true) : $batch->data;

            $batchSuccess = 0;
            $batchFailed = 0;
            $errors = [];

            foreach ($data as $rowIndex => $row) {
                try {
                    $settings = $row['_settings'] ?? [];
                    unset($row['_settings']);

                    // Handle blank values if updating
                    if (($settings['duplicate_action'] ?? '') === 'update' && empty($settings['overwrite_blank'])) {
                        $row = array_filter($row, function ($value) {
                            return $value !== null && $value !== '';
                        });
                    }

                    // Create the payload
                    $payload = new LeadIngestionPayload(
                        origin: 'csv_import',
                        leadData: $row,
                        metadata: ['import_id' => $import->id],
                        duplicateAction: $settings['duplicate_action'] ?? 'reject',
                        connectorId: 'local_import',
                        externalId: $row['external_id'] ?? null,
                    );

                    // Assign override logic
                    if (($settings['assignment'] ?? '') === 'user' && ! empty($settings['assign_to_user'])) {
                        $payload->leadData['user_id'] = $settings['assign_to_user'];
                    } elseif (($settings['assignment'] ?? '') === 'team' && ! empty($settings['assign_to_team'])) {
                        $payload->leadData['group_id'] = $settings['assign_to_team'];
                    }

                    // Source override
                    if (! empty($settings['source'])) {
                        $payload->sourceId = $settings['source'];
                    }

                    // Ensure emails and phones are formatted for IngestionService if mapped as flat strings
                    if (isset($payload->leadData['emails']) && is_string($payload->leadData['emails'])) {
                        $payload->leadData['emails'] = [['label' => 'work', 'value' => $payload->leadData['emails']]];
                    }
                    if (isset($payload->leadData['contact_numbers']) && is_string($payload->leadData['contact_numbers'])) {
                        $payload->leadData['contact_numbers'] = [['label' => 'work', 'value' => $payload->leadData['contact_numbers']]];
                    }

                    // Process via Ingestion Service
                    $result = $ingestionService->ingest($payload);

                    if ($result->wasRecentlyCreated) {
                        $summary['created']++;
                    } else {
                        $summary['updated']++;
                    }
                    $batchSuccess++;

                } catch (LeadIngestionException $e) {
                    if ($e->getMessage() === 'Duplicate lead detected.') {
                        $summary['duplicates']++;
                        $errors[] = "Row {$rowIndex}: Duplicate lead.";
                    } else {
                        $summary['failed']++;
                        $errors[] = "Row {$rowIndex}: ".$e->getMessage();
                    }
                    $batchFailed++;
                } catch (\Exception $e) {
                    $summary['failed']++;
                    $errors[] = "Row {$rowIndex}: ".$e->getMessage();
                    $batchFailed++;
                }
            }

            // Mark batch as processed and store errors
            $importBatchRepository->update([
                'state' => Import::STATE_PROCESSED,
                'summary' => [
                    'success' => $batchSuccess,
                    'failed' => $batchFailed,
                    'errors' => $errors,
                ],
            ], $batch->id);

            // Update master summary periodically
            $importRepository->update(['summary' => $summary], $import->id);
        }

        // Finish Import
        $importRepository->update([
            'state' => Import::STATE_PROCESSED,
            'summary' => $summary,
        ], $import->id);
    }
}
