<?php

namespace Webkul\Admin\Http\Controllers\Lead;

use Illuminate\Http\Request;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\Lead\Repositories\PipelineRepository;
use Webkul\Lead\Repositories\SourceRepository;
use Webkul\Lead\Services\LeadImportService;

class ImportWizardController extends Controller
{
    public function __construct(
        protected LeadImportService $leadImportService,
        protected AttributeRepository $attributeRepository,
        protected PipelineRepository $pipelineRepository,
        protected SourceRepository $sourceRepository
    ) {}

    /**
     * Display the import wizard UI.
     */
    public function index()
    {
        if (bouncer()->hasPermission('leads.import')) {
            // Need permission logic, assuming it's added.
        }

        $attributes = $this->attributeRepository->findWhere(['entity_type' => 'leads']);
        $pipelines = $this->pipelineRepository->all();
        $sources = $this->sourceRepository->all();

        return view('admin::leads.import.index', compact('attributes', 'pipelines', 'sources'));
    }

    /**
     * Handle CSV upload and return headers/preview.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240', // 10MB max
        ]);

        $file = $request->file('file');

        // Simple security check for content
        $content = file_get_contents($file->getRealPath());
        if (str_contains($content, '<?php')) {
            return response()->json(['message' => 'Invalid file content.'], 400);
        }

        $path = $file->storeAs('imports/leads', time().'_'.$file->getClientOriginalName());

        $result = $this->leadImportService->analyzeFile(storage_path('app/'.$path));

        return response()->json([
            'file_path' => $path,
            'headers' => $result['headers'],
            'preview_rows' => $result['preview_rows'],
            'total_rows' => $result['total_rows'],
        ]);
    }

    /**
     * Validate the mapping and run pre-flight duplicate check.
     */
    public function validateMapping(Request $request)
    {
        $data = $request->validate([
            'file_path' => 'required|string',
            'mapping' => 'required|array',
            'settings' => 'required|array',
        ]);

        $fullPath = storage_path('app/'.$data['file_path']);

        if (! file_exists($fullPath)) {
            return response()->json(['message' => 'File not found.'], 404);
        }

        $validationResult = $this->leadImportService->validateMapping($fullPath, $data['mapping'], $data['settings']);

        return response()->json($validationResult);
    }

    /**
     * Process the import (dispatch to queue).
     */
    public function process(Request $request)
    {
        $data = $request->validate([
            'file_path' => 'required|string',
            'mapping' => 'required|array',
            'settings' => 'required|array',
        ]);

        $fullPath = storage_path('app/'.$data['file_path']);

        try {
            $importBatchId = $this->leadImportService->dispatchImportJob($fullPath, $data['mapping'], $data['settings'], auth()->id());

            return response()->json([
                'message' => 'Import started successfully.',
                'batch_id' => $importBatchId,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Get the status of an import batch.
     */
    public function status($id)
    {
        $status = $this->leadImportService->getBatchStatus($id);

        return response()->json($status);
    }
}
