<?php

namespace Webkul\WebForm\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\Lead\Contracts\LeadIngestionService;
use Webkul\Lead\DataTransferObjects\LeadIngestionPayload;
use Webkul\Lead\Repositories\LeadRepository;
use Webkul\Lead\Repositories\PipelineRepository;
use Webkul\Lead\Repositories\SourceRepository;
use Webkul\Lead\Repositories\TypeRepository;
use Webkul\WebForm\Http\Requests\WebForm;
use Webkul\WebForm\Repositories\WebFormRepository;

class WebFormController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected AttributeRepository $attributeRepository,
        protected WebFormRepository $webFormRepository,
        protected LeadRepository $leadRepository,
        protected PipelineRepository $pipelineRepository,
        protected SourceRepository $sourceRepository,
        protected TypeRepository $typeRepository,
        protected LeadIngestionService $leadIngestionService
    ) {}

    /**
     * Remove the specified email template from storage.
     */
    public function formJS(string $formId): Response
    {
        $webForm = $this->webFormRepository->findOneByField('form_id', $formId);

        return response()->view('web_form::settings.web-forms.embed', compact('webForm'))
            ->header('Content-Type', 'text/javascript');
    }

    /**
     * Remove the specified email template from storage.
     */
    public function formStore(int $id): JsonResponse
    {
        app(WebForm::class);

        $webForm = $this->webFormRepository->findOrFail($id);

        if ($webForm->create_lead) {
            $data = request('leads') ?? [];

            $data['status'] = 1;

            // Set Pipeline
            $pipeline = $webForm->lead_pipeline_id
                ? $this->pipelineRepository->find($webForm->lead_pipeline_id)
                : null;
            if ($pipeline) {
                $data['lead_pipeline_id'] = $pipeline->id;
                $stage = $pipeline->stages()->first();
                if ($stage) {
                    $data['lead_pipeline_stage_id'] = $stage->id;
                }
            }

            // Set Title
            $data['title'] = request('leads.title') ?: 'Lead From Web Form';
            $data['lead_value'] = request('leads.lead_value') ?: 0;
            $data['lead_type_id'] = request('leads.lead_type_id') ?: $this->typeRepository->first()?->id;

            // Determine source
            $sourceId = request('leads.lead_source_id');
            if (! $sourceId) {
                $source = $this->sourceRepository->findOneByField('name', 'Web Form') ?? $this->sourceRepository->first();
                $sourceId = $source?->id;
            }

            try {
                $payload = new LeadIngestionPayload(
                    origin: 'webform',
                    sourceId: $sourceId,
                    leadData: $data,
                    metadata: [
                        'form_id' => $webForm->form_id,
                    ]
                );

                $this->leadIngestionService->ingest($payload);
            } catch (\Exception $e) {
                return response()->json([
                    'message' => $e->getMessage(),
                ], 400);
            }
        }

        if ($webForm->submit_success_action == 'message') {
            return response()->json([
                'message' => $webForm->submit_success_content,
            ], 200);
        } else {
            return response()->json([
                'redirect' => $webForm->submit_success_content,
            ], 301);
        }
    }

    /**
     * Remove the specified email template from storage.
     */
    public function preview(string $id): View
    {
        $webForm = $this->webFormRepository->findOneByField('form_id', $id);

        if (is_null($webForm)) {
            abort(404);
        }

        return view('web_form::settings.web-forms.preview', compact('webForm'));
    }

    /**
     * Preview the web form from datagrid.
     */
    public function view(int $id): View
    {
        $webForm = $this->webFormRepository->findOneByField('id', $id);

        request()->merge(['id' => $webForm->form_id]);

        if (is_null($webForm)) {
            abort(404);
        }

        return view('web_form::settings.web-forms.preview', compact('webForm'));
    }
}
