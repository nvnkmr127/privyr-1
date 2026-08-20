<?php

namespace Webkul\Admin\Http\Controllers\Lead;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Lead\Services\BulkLeadOperationService;

class BulkActionController extends Controller
{
    public function __construct(
        protected BulkLeadOperationService $bulkService
    ) {}

    /**
     * Execute a bulk action.
     */
    public function execute(Request $request): JsonResponse
    {
        $action = $request->query('action');
        
        $data = $request->validate([
            'indices' => 'required|array',
            'value' => 'nullable|string',
            'mode' => 'nullable|string',
            'filters' => 'nullable|array',
        ]);

        try {
            $result = $this->bulkService->execute(
                action: $action,
                indices: $data['indices'],
                value: $data['value'] ?? null,
                mode: $data['mode'] ?? 'none',
                filters: $data['filters'] ?? []
            );

            return response()->json([
                'message' => 'Bulk action executed successfully. ' . ($result['message'] ?? ''),
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to execute bulk action: ' . $e->getMessage(),
            ], 400);
        }
    }
}
