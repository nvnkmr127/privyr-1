<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LeadDistributionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Webkul\Contact\Repositories\PersonRepository;
use Webkul\Lead\Repositories\LeadRepository;
use Webkul\Lead\Repositories\PipelineRepository;
use Webkul\Lead\Repositories\SourceRepository;

class LeadCsvImportController extends Controller
{
    public function __construct(
        protected LeadRepository $leadRepository,
        protected PersonRepository $personRepository,
        protected SourceRepository $sourceRepository,
        protected PipelineRepository $pipelineRepository,
        protected LeadDistributionService $distributionService
    ) {}

    /**
     * Import array of rows with custom header mapping dictionary.
     */
    public function import(Request $request)
    {
        $request->validate([
            'rows' => 'required|array',
            'mapping' => 'required|array', // e.g. {"Client Name": "name", "Mobile": "phone", "Budget": "value"}
        ]);

        $rows = $request->input('rows');
        $mapping = $request->input('mapping');

        $pipeline = $this->pipelineRepository->getDefaultPipeline();
        $stage = $pipeline->stages->first();
        $source = $this->sourceRepository->findOneByField('name', 'CSV Import')
            ?? $this->sourceRepository->create(['name' => 'CSV Import']);

        $importedCount = 0;
        $skippedCount = 0;

        foreach ($rows as $row) {
            $extracted = [
                'name' => null,
                'phone' => null,
                'email' => null,
                'value' => 0,
                'title' => 'CSV Lead',
                'description' => '',
            ];

            foreach ($mapping as $csvCol => $targetField) {
                if (isset($row[$csvCol]) && array_key_exists($targetField, $extracted)) {
                    $extracted[$targetField] = $row[$csvCol];
                }
            }

            if (empty($extracted['phone']) && empty($extracted['email'])) {
                $skippedCount++;
                continue;
            }

            $person = null;
            if (!empty($extracted['phone'])) {
                $cleanPhone = preg_replace('/[^0-9]/', '', $extracted['phone']);
                $person = DB::table('persons')
                    ->whereRaw("REPLACE(REPLACE(REPLACE(contact_numbers, ' ', ''), '-', ''), '+', '') LIKE ?", ["%{$cleanPhone}%"])
                    ->first();
            }

            if (!$person && !empty($extracted['email'])) {
                $person = DB::table('persons')
                    ->where('emails', 'like', "%{$extracted['email']}%")
                    ->first();
            }

            $assignedUserId = $this->distributionService->getNextAssignedUserId();

            if (!$person) {
                $person = $this->personRepository->create([
                    'entity_type' => 'persons',
                    'name' => $extracted['name'] ?? 'CSV Prospect',
                    'emails' => !empty($extracted['email']) ? [['value' => $extracted['email'], 'label' => 'work']] : [],
                    'contact_numbers' => !empty($extracted['phone']) ? [['value' => $extracted['phone'], 'label' => 'mobile']] : [],
                    'user_id' => $assignedUserId,
                ]);
            }

            $this->leadRepository->create([
                'entity_type' => 'leads',
                'title' => ($extracted['title'] ?: 'CSV Lead') . ($extracted['name'] ? " - {$extracted['name']}" : ''),
                'description' => $extracted['description'],
                'lead_value' => (float) $extracted['value'],
                'user_id' => $assignedUserId,
                'person_id' => $person->id,
                'lead_source_id' => $source->id,
                'lead_pipeline_id' => $pipeline->id,
                'lead_pipeline_stage_id' => $stage->id,
                'status' => 1,
            ]);

            $importedCount++;
        }

        return response()->json([
            'status' => 'success',
            'message' => "CSV import complete: {$importedCount} leads imported, {$skippedCount} skipped due to missing contact details.",
            'data' => [
                'imported' => $importedCount,
                'skipped' => $skippedCount,
            ],
        ]);
    }
}
