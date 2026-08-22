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

    public $timeout = 600; // 10 minutes per batch

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $importBatchId,
        public int $importId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(
        ImportRepository $importRepository,
        ImportBatchRepository $importBatchRepository,
        LeadIngestionService $ingestionService
    ) {
        $import = $importRepository->find($this->importId);
        $batch = $importBatchRepository->find($this->importBatchId);

        if (! $import || ! $batch) {
            return;
        }

        if ($import->state === Import::STATE_PENDING) {
            $importRepository->update(['state' => Import::STATE_PROCESSING], $import->id);
        }

        if ($batch->state === Import::STATE_PROCESSED) {
            return; // Already processed
        }

        $summary = $import->summary ?? ['created' => 0, 'updated' => 0, 'failed' => 0, 'duplicates' => 0];

        $batchSummary = $batch->summary ?? [
            'last_processed_index' => -1,
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        $data = is_string($batch->data) ? json_decode($batch->data, true) : $batch->data;

        foreach ($data as $rowIndex => $row) {
            // Skip already processed rows
            if ($rowIndex <= $batchSummary['last_processed_index']) {
                continue;
            }

            try {
                $settings = $row['_settings'] ?? [];
                unset($row['_settings']);

                // Handle blank values if updating
                if (($settings['duplicate_action'] ?? '') === 'update' && empty($settings['overwrite_blank'])) {
                    $row = array_filter($row, function ($value) {
                        return $value !== null && $value !== '';
                    });
                }

                // Assign override logic
                if (($settings['assignment'] ?? '') === 'user' && ! empty($settings['assign_to_user'])) {
                    $row['user_id'] = $settings['assign_to_user'];
                } elseif (($settings['assignment'] ?? '') === 'team' && ! empty($settings['assign_to_team'])) {
                    $row['group_id'] = $settings['assign_to_team'];
                }

                // Ensure emails and phones are formatted for IngestionService if mapped as flat strings
                if (isset($row['emails']) && is_string($row['emails'])) {
                    $row['emails'] = [['label' => 'work', 'value' => $row['emails']]];
                }
                if (isset($row['phones']) && is_string($row['phones'])) {
                    $row['phones'] = [['label' => 'work', 'value' => $row['phones']]];
                }

                $sourceId = ! empty($settings['source']) ? $settings['source'] : null;

                // Create the payload
                $payload = new LeadIngestionPayload(
                    origin: 'csv_import',
                    sourceId: $sourceId,
                    externalId: $row['external_id'] ?? null,
                    leadData: $row,
                    metadata: ['import_id' => $import->id],
                    duplicateAction: $settings['duplicate_action'] ?? 'reject',
                    connectorId: 'local_import',
                );

                // Process via Ingestion Service
                $result = $ingestionService->ingest($payload);

                if ($result->wasRecentlyCreated) {
                    $summary['created']++;
                } else {
                    $summary['updated']++;
                }
                $batchSummary['success']++;

            } catch (LeadIngestionException $e) {
                if ($e->getMessage() === 'Duplicate lead detected.') {
                    $summary['duplicates']++;
                    $batchSummary['errors'][] = "Row {$rowIndex}: Duplicate lead.";
                } else {
                    $summary['failed']++;
                    $batchSummary['errors'][] = "Row {$rowIndex}: ".$e->getMessage();
                }
                $batchSummary['failed']++;
            } catch (\Exception $e) {
                $summary['failed']++;
                $batchSummary['errors'][] = "Row {$rowIndex}: ".$e->getMessage();
                $batchSummary['failed']++;
            }

            // Row-level checkpointing
            $batchSummary['last_processed_index'] = $rowIndex;

            $importBatchRepository->update([
                'summary' => $batchSummary,
            ], $batch->id);

            // Update master summary periodically per row or chunk, here we update it every row for precision
            // since we are checkpointing, but for performance, doing it at batch end is often enough for the master.
            // Let's do it per row to be safe as requested.
            $importRepository->update(['summary' => $summary], $import->id);
        }

        // Mark batch as processed
        $importBatchRepository->update([
            'state' => Import::STATE_PROCESSED,
        ], $batch->id);

        // Check if all batches are processed to finish Import
        $pendingBatchesCount = $importBatchRepository->count([
            'import_id' => $import->id,
            'state' => Import::STATE_PENDING,
        ]);

        if ($pendingBatchesCount === 0) {
            // Finish Import
            $importRepository->update([
                'state' => Import::STATE_PROCESSED,
                'summary' => $summary,
            ], $import->id);
        }
    }
}
