<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Webkul\Lead\Contracts\LeadIngestionService;
use Webkul\Lead\DataTransferObjects\LeadIngestionPayload;

class LeadCsvImportController extends Controller
{
    /**
     * Import array of rows with custom header mapping dictionary.
     */
    public function import(Request $request)
    {
        $request->validate([
            // Cap the batch so a single request can't exhaust memory/DB with a
            // synchronous ingest loop. ponytail: chunk + queue for larger imports.
            'rows' => 'required|array|max:1000',
            'mapping' => 'required|array', // e.g. {"Client Name": "name", "Mobile": "phone", "Budget": "value"}
        ]);

        $rows = $request->input('rows');
        $mapping = $request->input('mapping');

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

            $payload = LeadIngestionPayload::fromArray([
                'origin' => 'csv_import',
                'sourceName' => 'CSV Import',
                'duplicateAction' => 'update',
                'leadData' => [
                    'title' => ($extracted['title'] ?: 'CSV Lead').($extracted['name'] ? " - {$extracted['name']}" : ''),
                    'description' => $extracted['description'],
                    'lead_value' => (float) $extracted['value'],
                    'person_name' => $extracted['name'] ?? 'CSV Prospect',
                    'emails' => ! empty($extracted['email']) ? [['value' => $extracted['email'], 'label' => 'work']] : [],
                    'contact_numbers' => ! empty($extracted['phone']) ? [['value' => $extracted['phone'], 'label' => 'mobile']] : [],
                ],
                'metadata' => ['ip' => request()->ip()],
            ]);

            app(LeadIngestionService::class)->ingest($payload);

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
