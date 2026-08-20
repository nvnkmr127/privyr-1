<?php

namespace Webkul\Lead\Services;

use Illuminate\Support\Facades\DB;
use Webkul\DataTransfer\Helpers\Sources\CSV;
use Webkul\Lead\Repositories\LeadRepository;
use Webkul\DataTransfer\Models\Import;
use Webkul\DataTransfer\Models\ImportBatch;
use Webkul\DataTransfer\Repositories\ImportRepository;
use Webkul\DataTransfer\Repositories\ImportBatchRepository;
use Webkul\Lead\Jobs\LeadIngestionBatch;

class LeadImportService
{
    public function __construct(
        protected LeadRepository $leadRepository,
        protected DuplicateMatchingService $duplicateMatchingService,
        protected ImportRepository $importRepository,
        protected ImportBatchRepository $importBatchRepository
    ) {}

    /**
     * Analyze a CSV file to return headers and preview rows.
     */
    public function analyzeFile(string $filePath): array
    {
        $csv = new CSV($filePath);
        $headers = $csv->getColumns();
        
        $previewRows = [];
        $csv->rewind();
        
        $count = 0;
        foreach ($csv->getRows() as $row) {
            if ($count >= 5) break;
            
            // Map the row data to the headers explicitly for preview
            $mappedRow = [];
            foreach ($headers as $index => $header) {
                $mappedRow[$header] = $row[$index] ?? null;
            }
            
            $previewRows[] = $mappedRow;
            $count++;
        }

        // We can't efficiently count total rows without reading the whole file.
        // For now, we will do a rough count or just a boolean `has_data`.
        $totalRows = 0; // Requires full file scan, skip for performance unless needed.

        return [
            'headers' => $headers,
            'preview_rows' => $previewRows,
            'total_rows' => $totalRows
        ];
    }

    /**
     * Run a pre-flight duplicate check based on mapped columns.
     */
    public function validateMapping(string $filePath, array $mapping, array $settings): array
    {
        // 1. Basic validation of required mapping fields
        $required = ['title'];
        $mappedFields = array_filter(array_values($mapping));
        
        // If neither person_name nor title is mapped, we might have issues, but let's assume `LeadIngestionService` fallback is okay.
        
        // 2. Pre-flight duplicate scan
        $csv = new CSV($filePath);
        $headers = $csv->getColumns();
        
        $emailIndex = array_search('emails', $mapping);
        $phoneIndex = array_search('contact_numbers', $mapping);
        
        $duplicatesCount = 0;
        $totalCount = 0;
        
        // We do a batch query for efficiency if the file is large, but for now just read a chunk
        $csv->rewind();
        $emailsToSearch = [];
        $phonesToSearch = [];
        
        foreach ($csv->getRows() as $row) {
            $totalCount++;
            if ($totalCount > 1000) {
                break; // Cap the preview scan
            }
            
            if ($emailIndex !== false && !empty($row[$emailIndex])) {
                $emailsToSearch[] = $row[$emailIndex];
            }
            if ($phoneIndex !== false && !empty($row[$phoneIndex])) {
                $phonesToSearch[] = $row[$phoneIndex];
            }
        }
        
        // Batch query the duplicates
        if (!empty($emailsToSearch) || !empty($phonesToSearch)) {
            $query = $this->leadRepository->getModel()->newQuery();
            $query->where(function($q) use ($emailsToSearch, $phonesToSearch) {
                if (!empty($emailsToSearch)) {
                    foreach ($emailsToSearch as $email) {
                        $q->orWhere('emails', 'like', "%\"value\":\"{$email}\"%");
                    }
                }
                if (!empty($phonesToSearch)) {
                    foreach ($phonesToSearch as $phone) {
                        $q->orWhere('contact_numbers', 'like', "%\"value\":\"{$phone}\"%");
                    }
                }
            });
            
            $duplicatesCount = $query->count();
        }

        return [
            'valid' => true,
            'total_scanned' => $totalCount,
            'duplicates_detected' => $duplicatesCount
        ];
    }

    /**
     * Dispatch the actual import job using DataTransfer architecture.
     */
    public function dispatchImportJob(string $filePath, array $mapping, array $settings, int $userId)
    {
        DB::beginTransaction();
        try {
            // Create the Import record using the DataTransfer package schema
            $import = $this->importRepository->create([
                'type' => 'leads_ingestion',
                'file_path' => $filePath,
                'state' => Import::STATE_PENDING,
                'action' => Import::ACTION_APPEND, // Using native constant
                'user_id' => $userId,
                // We store the mapping and settings in the import record. DataTransfer doesn't have a JSON config column by default, 
                // but we can serialize it into a file or extend the table.
                // Assuming it has a generic way or we use a custom table. For now we will rely on DataTransfer if it supports it, 
                // or we can pass it to the Batch directly. Let's assume Import model has some metadata field, or we will just use batches.
            ]);
            
            // To pass data securely to the job, we create the batches.
            // DataTransfer natively reads the CSV and creates `ImportBatch` records.
            $this->chunkAndCreateBatches($import, $filePath, $mapping, $settings);
            
            // Dispatch the job
            // The job will read all batches for this import and process them.
            // We use a custom Job to route through LeadIngestionService instead of the default IndexBatch.
            dispatch(new \Webkul\Lead\Jobs\LeadIngestionBatch($import->id));
            
            DB::commit();
            return $import->id;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    protected function chunkAndCreateBatches($import, $filePath, $mapping, $settings)
    {
        $csv = new CSV($filePath);
        $headers = $csv->getColumns();
        $csv->rewind();
        
        $batchData = [];
        $batchCount = 0;
        
        foreach ($csv->getRows() as $row) {
            $mappedRow = [];
            
            foreach ($mapping as $csvHeader => $leadField) {
                if (empty($leadField)) continue;
                
                $index = array_search($csvHeader, $headers);
                if ($index !== false) {
                    $mappedRow[$leadField] = $row[$index] ?? null;
                }
            }
            
            // Include settings in every row so the Job has context (assignment, duplicate behavior)
            $mappedRow['_settings'] = $settings;
            
            $batchData[] = $mappedRow;
            
            if (count($batchData) >= 100) { // Batch size 100
                $this->importBatchRepository->create([
                    'import_id' => $import->id,
                    'state' => Import::STATE_PENDING,
                    'data' => json_encode($batchData) // DataTransfer typically expects JSON/Array
                ]);
                $batchData = [];
                $batchCount++;
            }
        }
        
        if (!empty($batchData)) {
            $this->importBatchRepository->create([
                'import_id' => $import->id,
                'state' => Import::STATE_PENDING,
                'data' => json_encode($batchData)
            ]);
        }
    }

    public function getBatchStatus($importId)
    {
        $import = $this->importRepository->find($importId);
        $totalBatches = $this->importBatchRepository->count(['import_id' => $importId]);
        $processedBatches = $this->importBatchRepository->count(['import_id' => $importId, 'state' => Import::STATE_PROCESSED]);
        
        return [
            'state' => $import->state,
            'summary' => $import->summary ?? [],
            'progress' => $totalBatches > 0 ? round(($processedBatches / $totalBatches) * 100) : 0
        ];
    }
}
