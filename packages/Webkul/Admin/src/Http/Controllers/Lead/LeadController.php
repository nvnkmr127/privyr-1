<?php

namespace Webkul\Admin\Http\Controllers\Lead;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Prettus\Repository\Criteria\RequestCriteria;
use Webkul\Admin\DataGrids\Lead\LeadDataGrid;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Admin\Http\Requests\LeadForm;
use Webkul\Admin\Http\Requests\MassDestroyRequest;
use Webkul\Admin\Http\Requests\MassUpdateRequest;
use Webkul\Admin\Http\Resources\LeadResource;
use Webkul\Admin\Http\Resources\StageResource;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\DataGrid\ColumnTypes\Date as DateColumn;
use Webkul\DataGrid\Enums\DateRangeOptionEnum;
use Webkul\Lead\Helpers\MagicAI;
use Webkul\Lead\Repositories\LeadAssignmentRepository;
use Webkul\Lead\Repositories\LeadRepository;
use Webkul\Lead\Repositories\PipelineRepository;
use Webkul\Lead\Repositories\SourceRepository;
use Webkul\Lead\Repositories\StageRepository;
use Webkul\Lead\Repositories\TypeRepository;
use Webkul\Lead\Services\LeadFollowUpService;
use Webkul\Lead\Services\MagicAIService;
use Webkul\Tag\Repositories\TagRepository;
use Webkul\User\Repositories\UserRepository;

class LeadController extends Controller
{
    /**
     * Const variable for supported types.
     */
    const SUPPORTED_TYPES = 'pdf,bmp,jpeg,jpg,png,webp';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected UserRepository $userRepository,
        protected AttributeRepository $attributeRepository,
        protected SourceRepository $sourceRepository,
        protected TypeRepository $typeRepository,
        protected PipelineRepository $pipelineRepository,
        protected StageRepository $stageRepository,
        protected LeadRepository $leadRepository,
        protected \Webkul\Lead\Services\LeadDuplicateService $leadDuplicateService
    ) {
        request()->request->add(['entity_type' => 'leads']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(LeadDataGrid::class)->process();
        }

        if (request('pipeline_id')) {
            $pipeline = $this->pipelineRepository->find(request('pipeline_id'));
        } else {
            $pipeline = $this->pipelineRepository->getDefaultPipeline();
        }

        return view('admin::leads.index', [
            'pipeline' => $pipeline,
            'columns' => $this->getKanbanColumns(),
        ]);
    }

    /**
     * Display Unified Lead Inbox page.
     */
    public function inbox()
    {
        return view('admin::leads.inbox', [
            'sources' => $this->sourceRepository->all(),
            'users' => $this->userRepository->all(),
            'pipelines' => $this->pipelineRepository->all(),
            'stages' => $this->stageRepository->all(),
            'currentPreset' => request('preset', 'all'),
        ]);
    }

    /**
     * Display a listing of nurturing leads.
     */
    public function nurturing()
    {
        if (request()->ajax()) {
            return datagrid(\Webkul\Admin\DataGrids\Lead\NurtureDataGrid::class)->process();
        }

        return view('admin::leads.nurturing');
    }

    /**
     * Fetch JSON lead data for Lead Inbox.
     */
    public function inboxData(): JsonResponse
    {
        $preset = request('preset', 'all');
        $search = request('search');
        $filters = request()->only([
            'priority',
            'lead_source_id',
            'user_id',
            'lead_pipeline_stage_id',
        ]);

        $leads = $this->leadRepository->getInboxLeads($preset, $search, $filters, 20);

        return response()->json([
            'data' => $leads->items(),
            'meta' => [
                'current_page' => $leads->currentPage(),
                'last_page' => $leads->lastPage(),
                'per_page' => $leads->perPage(),
                'total' => $leads->total(),
            ],
        ]);
    }

    /**
     * Handle single swipe action on lead.
     */
    public function swipeAction(): JsonResponse
    {
        $request = request();
        $leadId = $request->input('lead_id');
        $action = $request->input('action');

        $lead = $this->leadRepository->find($leadId);

        if (! $lead) {
            return response()->json(['message' => trans('admin::app.leads.inbox.lead-not-found')], 404);
        }

        switch ($action) {
            case 'mark_read':
                $lead->update(['is_unread' => false]);
                break;
            case 'mark_unread':
                $lead->update(['is_unread' => true]);
                break;
            case 'mark_contacted':
                $lead->update([
                    'last_contacted_at' => Carbon::now(),
                    'is_unread' => false,
                ]);
                break;
            case 'schedule_follow_up':
                $lead->update([
                    'next_follow_up_at' => $request->input('date') ? Carbon::parse($request->input('date')) : Carbon::tomorrow(),
                ]);
                break;
            case 'archive':
                $lead->update(['is_archived' => true]);
                break;
            case 'unarchive':
                $lead->update(['is_archived' => false]);
                break;
            case 'change_stage':
                if ($stageId = $request->input('stage_id')) {
                    $this->leadRepository->updateStage(['lead_pipeline_stage_id' => $stageId], $leadId);
                }
                break;
            case 'change_priority':
                if ($priority = $request->input('priority')) {
                    $lead->update(['priority' => $priority]);
                }
                break;
        }

        return response()->json([
            'message' => trans('admin::app.leads.inbox.action-success'),
            'lead' => $lead->fresh(['user', 'stage', 'source', 'type', 'tags']),
        ]);
    }

    /**
     * Handle bulk actions on multiple leads.
     */
    public function bulkAction(): JsonResponse
    {
        $leadIds = request()->input('lead_ids', []);
        $action = request()->input('action');

        if (empty($leadIds) || ! is_array($leadIds)) {
            return response()->json(['message' => trans('admin::app.leads.inbox.select-leads')], 400);
        }

        switch ($action) {
            case 'archive':
                $this->leadRepository->getModel()->whereIn('id', $leadIds)->update(['is_archived' => true]);
                break;
            case 'unarchive':
                $this->leadRepository->getModel()->whereIn('id', $leadIds)->update(['is_archived' => false]);
                break;
            case 'mark_read':
                $this->leadRepository->getModel()->whereIn('id', $leadIds)->update(['is_unread' => false]);
                break;
            case 'change_priority':
                if ($priority = request()->input('priority')) {
                    $this->leadRepository->getModel()->whereIn('id', $leadIds)->update(['priority' => $priority]);
                }
                break;
            case 'reassign':
                if ($userId = request()->input('user_id')) {
                    $leads = $this->leadRepository->getModel()->whereIn('id', $leadIds)->get();
                    foreach ($leads as $lead) {
                        $previousOwner = $lead->user_id;
                        $lead->update(['user_id' => $userId]);

                        app(LeadAssignmentRepository::class)->create([
                            'lead_id' => $lead->id,
                            'assigned_to' => $userId,
                            'assigned_by' => auth()->check() ? auth()->id() : null,
                            'previous_owner' => $previousOwner,
                            'reason' => 'Bulk Reassignment',
                        ]);
                    }
                }
                break;
            case 'change_stage':
                if ($stageId = request()->input('stage_id')) {
                    $this->leadRepository->getModel()->whereIn('id', $leadIds)->update(['lead_pipeline_stage_id' => $stageId]);
                }
                break;
            case 'delete':
                foreach ($leadIds as $id) {
                    $this->leadRepository->delete($id);
                }
                break;
        }

        return response()->json([
            'message' => trans('admin::app.leads.inbox.bulk-success'),
        ]);
    }

    /**
     * Returns a listing of the resource.
     */
    public function get(): JsonResponse
    {
        if (request()->query('pipeline_id')) {
            $pipeline = $this->pipelineRepository->find(request()->query('pipeline_id'));
        } else {
            $pipeline = $this->pipelineRepository->getDefaultPipeline();
        }

        if (request()->query('pipeline_stage_id')) {
            $stages = $pipeline->stages->where('id', request()->query('pipeline_stage_id'));
        } else {
            $stages = $pipeline->stages;
        }

        foreach ($stages as $stage) {
            /**
             * We have to create a new instance of the lead repository every time, which is
             * why we're not using the injected one.
             */
            $query = app(LeadRepository::class)
                ->pushCriteria(app(RequestCriteria::class))
                ->where([
                    'lead_pipeline_id' => $pipeline->id,
                    'lead_pipeline_stage_id' => $stage->id,
                    'leads.is_archived' => 0,
                ]);

            if ($userIds = bouncer()->getAuthorizedUserIds()) {
                $query->whereIn('leads.user_id', $userIds);
            }

            $this->applyDateRangeFilters($query);

            // Apply Advanced Filters if present
            if ($requestedFilters = request()->input('filters')) {
                $matchType = request()->input('match_type') === 'any' ? 'any' : 'all';
                $availableColumns = $this->getKanbanColumns();
                app(\Webkul\Lead\Services\LeadFilterService::class)->applyAdvancedFilters($query, $requestedFilters, $matchType, $availableColumns);
            }

            $stage->lead_value = (clone $query)->sum('lead_value');

            $data[$stage->sort_order] = (new StageResource($stage))->jsonSerialize();

            $data[$stage->sort_order]['leads'] = [
                'data' => LeadResource::collection($paginator = $query->with([
                    'tags',
                    'type',
                    'source',
                    'user',
                    'group',
                    'pipeline',
                    'pipeline.stages',
                    'stage',
                    'attribute_values',
                ])->orderBy('updated_at', 'desc')->paginate(10)),

                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'from' => $paginator->firstItem(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'to' => $paginator->lastItem(),
                    'total' => $paginator->total(),
                ],
            ];
        }

        return response()->json($data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $pipelineId = request('pipeline_id');
        if (! $pipelineId) {
            $pipelineId = $this->pipelineRepository->getDefaultPipeline()->id;
        }

        $attributes = $this->attributeRepository
            ->where('entity_type', 'leads')
            ->where(function ($query) use ($pipelineId) {
                $query->whereNull('lead_pipeline_id')
                    ->orWhere('lead_pipeline_id', $pipelineId);
            })
            ->where(function ($query) {
                $query->whereIn('code', ['description', 'title', 'lead_value', 'lead_type_id', 'lead_source_id', 'expected_close_date', 'user_id'])
                    ->orWhere('is_user_defined', 1);
            })
            ->orderBy('sort_order', 'asc')
            ->get();

        return view('admin::leads.create', compact('attributes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(LeadForm $request): RedirectResponse|JsonResponse
    {
        Event::dispatch('lead.create.before');

        $data = $request->all();

        $data['status'] = \Webkul\Lead\Services\LeadLifecycleService::STATUS_OPEN;

        if (! request()->has('bypass_duplicate_warning')) {
            $origin = 'manual';
            $duplicateResult = $this->leadDuplicateService->detect($data, $origin);
            if ($duplicateResult) {
                if (request()->ajax()) {
                    return response()->json([
                        'is_duplicate_warning' => true,
                        'message' => 'Potential duplicate lead detected.',
                        'duplicate_info' => $duplicateResult,
                    ], 409);
                } else {
                    session()->flash('warning', 'Potential duplicate lead detected.');
                    // In a non-ajax scenario, we'd normally redirect back with data, but Krayin mostly uses Ajax for forms.
                    return redirect()->back()->withInput();
                }
            }
        }

        if (request()->has('quick_add') && empty($data['user_id'])) {
            $data['user_id'] = auth()->guard('user')->user()->id;
        }

        if (! empty($data['lead_pipeline_stage_id'])) {
            $stage = $this->stageRepository->findOrFail($data['lead_pipeline_stage_id']);

            $data['lead_pipeline_id'] = $stage->lead_pipeline_id;
        } else {
            if (empty($data['lead_pipeline_id'])) {
                $pipeline = $this->pipelineRepository->getDefaultPipeline();

                $data['lead_pipeline_id'] = $pipeline->id;
            } else {
                $pipeline = $this->pipelineRepository->findOrFail($data['lead_pipeline_id']);
            }

            $stage = $pipeline->stages()->first();

            $data['lead_pipeline_stage_id'] = $stage->id;
        }

        if (in_array($stage->code, ['won', 'lost'])) {
            $data['closed_at'] = Carbon::now();
        }

        $lead = $this->leadRepository->create($data);

        if (request()->ajax()) {
            return response()->json([
                'message' => trans('admin::app.leads.create-success'),
                'data' => new LeadResource($lead),
            ]);
        }

        Event::dispatch('lead.create.after', $lead);

        session()->flash('success', trans('admin::app.leads.create-success'));

        if (! empty($data['lead_pipeline_id'])) {
            $params['pipeline_id'] = $data['lead_pipeline_id'];
        }

        return redirect()->route('admin.leads.index', $params ?? []);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View
    {
        $lead = $this->leadRepository->findOrFail($id);

        $pipelineId = $lead->lead_pipeline_id;

        $attributes = $this->attributeRepository
            ->where('entity_type', 'leads')
            ->where(function ($query) use ($pipelineId) {
                $query->whereNull('lead_pipeline_id')
                    ->orWhere('lead_pipeline_id', $pipelineId);
            })
            ->where(function ($query) {
                $query->whereIn('code', ['description', 'title', 'lead_value', 'lead_type_id', 'lead_source_id', 'expected_close_date', 'user_id'])
                    ->orWhere('is_user_defined', 1);
            })
            ->orderBy('sort_order', 'asc')
            ->get();

        $this->authorize('update', $lead);

        return view('admin::leads.edit', compact('lead', 'attributes'));
    }

    /**
     * Display a resource.
     */
    public function view(int $id)
    {
        $lead = $this->leadRepository->findOrFail($id);

        $this->authorize('view', $lead);

        return view('admin::leads.view', compact('lead'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(LeadForm $request, int $id): RedirectResponse|JsonResponse
    {
        $lead = $this->leadRepository->findOrFail($id);
        $this->authorize('update', $lead);

        Event::dispatch('lead.update.before', $id);

        $data = $request->all();

        if (isset($data['lead_pipeline_stage_id'])) {
            $stage = $this->stageRepository->findOrFail($data['lead_pipeline_stage_id']);

            $data['lead_pipeline_id'] = $stage->lead_pipeline_id;
        } else {
            $pipeline = $this->pipelineRepository->getDefaultPipeline();

            $stage = $pipeline->stages()->first();

            $data['lead_pipeline_id'] = $pipeline->id;

            $data['lead_pipeline_stage_id'] = $stage->id;
        }

        $lead = $this->leadRepository->update($data, $id);

        Event::dispatch('lead.update.after', $lead);

        if (request()->ajax()) {
            return response()->json([
                'message' => trans('admin::app.leads.update-success'),
            ]);
        }

        session()->flash('success', trans('admin::app.leads.update-success'));

        if (request()->has('closed_at')) {
            return redirect()->back();
        } else {
            return redirect()->route('admin.leads.index', $data['lead_pipeline_id']);
        }
    }

    /**
     * Update the lead attributes.
     */
    public function updateAttributes(int $id)
    {
        $lead = $this->leadRepository->findOrFail($id);
        $this->authorize('update', $lead);

        $data = request()->all();

        $attributes = $this->attributeRepository->findWhere([
            'entity_type' => 'leads',
            ['code', 'NOTIN', ['title', 'description']],
        ]);

        Event::dispatch('lead.update.before', $id);

        $lead = $this->leadRepository->update($data, $id, $attributes);

        Event::dispatch('lead.update.after', $lead);

        return response()->json([
            'message' => trans('admin::app.leads.update-success'),
        ]);
    }

    /**
     * Update the lead stage.
     */
    public function updateStage(int $id)
    {
        $this->validate(request(), [
            'lead_pipeline_stage_id' => 'required',
        ]);

        $lead = $this->leadRepository->findOrFail($id);

        $this->authorize('update', $lead);

        $stage = $lead->pipeline->stages()
            ->where('id', request()->input('lead_pipeline_stage_id'))
            ->firstOrFail();

        Event::dispatch('lead.update.before', $id);

        $payload = request()->merge([
            'entity_type' => 'leads',
            'lead_pipeline_stage_id' => $stage->id,
        ])->only([
            'closed_at',
            'lost_reason',
            'lead_pipeline_stage_id',
            'entity_type',
        ]);

        $lead = $this->leadRepository->update($payload, $id, ['lead_pipeline_stage_id']);

        Event::dispatch('lead.update.after', $lead);

        return response()->json([
            'message' => trans('admin::app.leads.update-success'),
        ]);
    }

    /**
     * Search person results.
     */
    public function search(): AnonymousResourceCollection
    {
        if ($userIds = bouncer()->getAuthorizedUserIds()) {
            $results = $this->leadRepository
                ->pushCriteria(app(RequestCriteria::class))
                ->findWhereIn('user_id', $userIds);
        } else {
            $results = $this->leadRepository
                ->pushCriteria(app(RequestCriteria::class))
                ->all();
        }

        return LeadResource::collection($results);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $lead = $this->leadRepository->findOrFail($id);
        $this->authorize('delete', $lead);

        try {
            Event::dispatch('lead.delete.before', $id);

            $this->leadRepository->delete($id);

            Event::dispatch('lead.delete.after', $id);

            return response()->json([
                'message' => trans('admin::app.leads.destroy-success'),
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => trans('admin::app.leads.destroy-failed'),
            ], 400);
        }
    }

    /**
     * Update the lead status.
     */
    public function updateStatus(int $id): \Illuminate\Http\JsonResponse
    {
        $lead = $this->leadRepository->findOrFail($id);
        $this->authorize('update', $lead);
        
        $lead = $this->leadRepository->findOrFail($id);

        try {
            $status = request()->input('status');
            $reason = request()->input('reason');
            
            $lifecycleService = app(\Webkul\Lead\Services\LeadLifecycleService::class);
            
            if ($status === 'Reopen') {
                $lifecycleService->reopenLead($lead, $reason);
            } else {
                $lifecycleService->changeStatus($lead, $status, $reason);
            }

            return response()->json([
                'message' => trans('admin::app.leads.update-success'),
            ]);
        } catch (\Exception $exception) {
        } catch (\Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 400);
        }
    }

    /**
     * Move a lead to Nurturing status.
     */
    public function nurture(int $id): JsonResponse
    {
        $this->preventUnauthorizedAccess($this->leadRepository->findOrFail($id)->user_id);
        
        $data = request()->validate([
            'nurture_reason_id' => 'required|integer',
            'nurture_reengagement_date' => 'required|date',
            'nurture_notes' => 'nullable|string',
            'create_follow_up' => 'nullable|boolean',
        ]);

        $lead = $this->leadRepository->findOrFail($id);

        try {
            $nurtureService = app(\Webkul\Lead\Services\LeadNurtureService::class);
            $nurtureService->startNurturing($lead, $data);

            return response()->json([
                'message' => 'Lead moved to Nurturing successfully.',
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 400);
        }
    }

    /**
     * End lead's Nurturing status.
     */
    public function endNurture(int $id): JsonResponse
    {
        $this->preventUnauthorizedAccess($this->leadRepository->findOrFail($id)->user_id);
        
        $data = request()->validate([
            'outcome' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $lead = $this->leadRepository->findOrFail($id);

        try {
            $nurtureService = app(\Webkul\Lead\Services\LeadNurtureService::class);
            $nurtureService->completeNurturing($lead, $data['outcome'], $data['notes']);

            return response()->json([
                'message' => 'Lead Nurturing ended successfully.',
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 400);
        }
    }

    /**
     * Mass update the specified resources.
     */
    public function massUpdate(MassUpdateRequest $massUpdateRequest): JsonResponse
    {
        $leads = $this->filterAuthorizedRecords(
            $this->leadRepository->findWhereIn('id', $massUpdateRequest->input('indices'))
        );

        try {
            foreach ($leads as $lead) {
                Event::dispatch('lead.update.before', $lead->id);

                $lead = $this->leadRepository->find($lead->id);

                $lead?->update(['lead_pipeline_stage_id' => $massUpdateRequest->input('value')]);

                Event::dispatch('lead.update.before', $lead->id);
            }

            return response()->json([
                'message' => trans('admin::app.leads.update-success'),
            ]);
        } catch (\Exception $th) {
            return response()->json([
                'message' => trans('admin::app.leads.update-failed'),
            ], 400);
        }
    }

    /**
     * Mass delete the specified resources.
     */
    public function massDestroy(MassDestroyRequest $massDestroyRequest): JsonResponse
    {
        $leads = $this->filterAuthorizedRecords(
            $this->leadRepository->findWhereIn('id', $massDestroyRequest->input('indices'))
        );

        try {
            foreach ($leads as $lead) {
                Event::dispatch('lead.delete.before', $lead->id);

                $this->leadRepository->delete($lead->id);

                Event::dispatch('lead.delete.after', $lead->id);
            }

            return response()->json([
                'message' => trans('admin::app.leads.destroy-success'),
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => trans('admin::app.leads.destroy-failed'),
            ]);
        }
    }

    /**
     * Assign lead manually.
     */
    public function assign(int $id): RedirectResponse|JsonResponse
    {
        $this->preventUnauthorizedAccess($this->leadRepository->findOrFail($id)->user_id);

        $data = request()->validate([
            'user_id' => 'nullable|integer',
            'group_id' => 'nullable|integer',
        ]);

        $lead = $this->leadRepository->findOrFail($id);
        
        $assignmentService = app(\Webkul\Lead\Services\LeadAssignmentService::class);
        
        if (empty($data['user_id']) && empty($data['group_id'])) {
            $assignmentService->unassign($lead);
        } else {
            $assignmentService->assignManually($lead, $data['user_id'] ?? null, $data['group_id'] ?? null);
        }

        if (request()->ajax()) {
            return response()->json([
                'message' => 'Lead assigned successfully.',
            ]);
        }

        session()->flash('success', 'Lead assigned successfully.');
        return redirect()->back();
    }

    /**
     * Mass reassign the specified resources.
     */
    public function massReassign(\Illuminate\Http\Request $request): JsonResponse
    {
        $data = $request->validate([
            'indices' => 'required|array',
            'value' => 'required|string',
        ]);

        $leads = $this->filterAuthorizedRecords(
            $this->leadRepository->findWhereIn('id', $data['indices'])
        );

        $assignmentService = app(\Webkul\Lead\Services\LeadAssignmentService::class);
        $userId = null;
        $groupId = null;

        if (str_starts_with($data['value'], 'user_')) {
            $userId = (int) str_replace('user_', '', $data['value']);
        } elseif (str_starts_with($data['value'], 'group_')) {
            $groupId = (int) str_replace('group_', '', $data['value']);
        }

        try {
            foreach ($leads as $lead) {
                if (empty($userId) && empty($groupId)) {
                    $assignmentService->unassign($lead);
                } else {
                    $assignmentService->assignManually($lead, $userId, $groupId);
                }
            }

            return response()->json([
                'message' => 'Leads reassigned successfully.',
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => trans('admin::app.leads.update-failed'),
            ]);
        }
    }

    /**
     * Kanban lookup.
     */
    public function kanbanLookup()
    {
        $params = $this->validate(request(), [
            'column' => ['required'],
            'search' => ['required', 'min:2'],
        ]);

        /**
         * Finding the first column from the collection.
         */
        $column = collect($this->getKanbanColumns())->where('index', $params['column'])->firstOrFail();

        /**
         * Fetching on the basis of column options.
         */
        return app($column['filterable_options']['repository'])
            ->select([$column['filterable_options']['column']['label'].' as label', $column['filterable_options']['column']['value'].' as value'])
            ->where($column['filterable_options']['column']['label'], 'LIKE', '%'.$params['search'].'%')
            ->get()
            ->map
            ->only('label', 'value');
    }

    /**
     * Get columns for the kanban view.
     */
    private function getKanbanColumns(): array
    {
        return [
            [
                'index' => 'id',
                'label' => trans('admin::app.leads.index.kanban.columns.id'),
                'type' => 'integer',
                'searchable' => false,
                'search_field' => 'in',
                'filterable' => true,
                'filterable_type' => null,
                'filterable_options' => [],
                'allow_multiple_values' => true,
                'sortable' => true,
                'visibility' => true,
            ],
            [
                'index' => 'lead_value',
                'label' => trans('admin::app.leads.index.kanban.columns.lead-value'),
                'type' => 'string',
                'searchable' => false,
                'search_field' => 'in',
                'filterable' => true,
                'filterable_type' => null,
                'filterable_options' => [],
                'allow_multiple_values' => true,
                'sortable' => true,
                'visibility' => true,
            ],
            [
                'index' => 'user_id',
                'label' => trans('admin::app.leads.index.kanban.columns.sales-person'),
                'type' => 'string',
                'searchable' => false,
                'search_field' => 'in',
                'filterable' => true,
                'filterable_type' => 'searchable_dropdown',
                'filterable_options' => [
                    'repository' => UserRepository::class,
                    'column' => [
                        'label' => 'name',
                        'value' => 'id',
                    ],
                ],
                'allow_multiple_values' => true,
                'sortable' => true,
                'visibility' => true,
            ],
            [
                'index' => 'person_name',
                'label' => trans('admin::app.leads.index.kanban.columns.contact-person'),
                'type' => 'string',
                'searchable' => true,
                'search_field' => 'like',
                'filterable' => true,
                'allow_multiple_values' => false,
                'sortable' => true,
                'visibility' => true,
            ],
            [
                'index' => 'lead_type_id',
                'label' => trans('admin::app.leads.index.kanban.columns.lead-type'),
                'type' => 'string',
                'searchable' => false,
                'search_field' => 'in',
                'filterable' => true,
                'filterable_type' => 'dropdown',
                'filterable_options' => $this->typeRepository->all(['name as label', 'id as value'])->toArray(),
                'allow_multiple_values' => true,
                'sortable' => true,
                'visibility' => true,
            ],
            [
                'index' => 'lead_source_id',
                'label' => trans('admin::app.leads.index.kanban.columns.source'),
                'type' => 'string',
                'searchable' => false,
                'search_field' => 'in',
                'filterable' => true,
                'filterable_type' => 'dropdown',
                'filterable_options' => $this->sourceRepository->all(['name as label', 'id as value'])->toArray(),
                'allow_multiple_values' => true,
                'sortable' => true,
                'visibility' => true,
            ],
            [
                'index' => 'tags.name',
                'label' => trans('admin::app.leads.index.kanban.columns.tags'),
                'type' => 'string',
                'searchable' => false,
                'search_field' => 'in',
                'filterable' => true,
                'allow_multiple_values' => true,
                'sortable' => true,
                'visibility' => true,
                'filterable_type' => 'searchable_dropdown',
                'filterable_options' => [
                    'repository' => TagRepository::class,
                    'column' => [
                        'label' => 'name',
                        'value' => 'name',
                    ],
                ],
            ],
            [
                'index' => 'expected_close_date',
                'label' => trans('admin::app.leads.index.kanban.columns.date-to'),
                'type' => 'date',
                'searchable' => false,
                'search_field' => 'between',
                'filterable' => true,
                'filterable_type' => 'date_range',
                'filterable_options' => DateRangeOptionEnum::options(),
                'allow_multiple_values' => true,
                'sortable' => true,
                'visibility' => true,
            ],
            [
                'index' => 'created_at',
                'label' => trans('admin::app.leads.index.kanban.columns.created-at'),
                'type' => 'date',
                'searchable' => false,
                'search_field' => 'between',
                'filterable' => true,
                'filterable_type' => 'date_range',
                'filterable_options' => DateRangeOptionEnum::options(),
                'allow_multiple_values' => true,
                'sortable' => true,
                'visibility' => true,
            ],
        ];
    }

    /**
     * Apply the kanban date range filters to the given lead query. These filters are sent as a
     * dedicated parameter rather than through the search string, so they are applied here.
     *
     * The datagrid's date column type is reused to resolve the requested value, which keeps the
     * quick filter options, the partial ranges and the day boundaries consistent between the
     * kanban and the lead listing.
     *
     * @param  mixed  $query
     */
    private function applyDateRangeFilters($query): void
    {
        foreach ($this->getKanbanDateColumns() as $index => $columnName) {
            $requestedDates = request($index);

            if (empty($requestedDates)) {
                continue;
            }

            $column = new DateColumn([
                'index' => $index,
                'label' => $index,
                'type' => 'date',
                'filterable' => true,
                'filterable_type' => 'date_range',
            ]);

            $column->setColumnName($columnName);

            $column->processFilter($query, $requestedDates);
        }
    }

    /**
     * Returns the kanban date columns, mapped to their qualified table column name.
     */
    private function getKanbanDateColumns(): array
    {
        return [
            'expected_close_date' => 'leads.expected_close_date',
            'created_at' => 'leads.created_at',
        ];
    }

    /**
     * Create lead with specified AI.
     */
    public function createByAI()
    {
        $leadData = [];

        $errorMessages = [];

        foreach (request()->file('files') as $file) {
            $lead = $this->processFile($file);

            if (
                isset($lead['status'])
                && $lead['status'] === 'error'
            ) {
                $errorMessages[] = $lead['message'];
            } else {
                $leadData[] = $lead;
            }
        }

        if (isset($errorMessages[0]['code'])) {
            return response()->json(MagicAI::errorHandler($errorMessages[0]['message']));
        }

        if (
            empty($leadData)
            && ! empty($errorMessages)
        ) {
            return response()->json(MagicAI::errorHandler(implode(', ', $errorMessages)), 400);
        }

        if (empty($leadData)) {
            return response()->json(MagicAI::errorHandler(trans('admin::app.leads.no-valid-files')), 400);
        }

        return response()->json([
            'message' => trans('admin::app.leads.create-success'),
            'leads' => $this->createLeads($leadData),
        ]);
    }

    /**
     * Process file.
     *
     * @param  mixed  $file
     */
    private function processFile($file)
    {
        $validator = Validator::make(
            ['file' => $file],
            ['file' => 'required|extensions:'.str_replace(' ', '', self::SUPPORTED_TYPES)]
        );

        if ($validator->fails()) {
            return MagicAI::errorHandler($validator->errors()->first());
        }

        $base64Pdf = base64_encode(file_get_contents($file->getRealPath()));

        $extractedData = MagicAIService::extractDataFromFile($base64Pdf);

        $lead = MagicAI::mapAIDataToLead($extractedData);

        return $lead;
    }

    /**
     * Create multiple leads.
     */
    private function createLeads($rawLeads): array
    {
        $leads = [];

        foreach ($rawLeads as $rawLead) {
            Event::dispatch('lead.create.before');

            foreach ($rawLead['person']['emails'] as $email) {
                $person = $this->personRepository
                    ->whereJsonContains('emails', [['value' => $email['value']]])
                    ->first();

                if ($person) {
                    $rawLead['person']['id'] = $person->id;

                    break;
                }
            }

            $pipeline = $this->pipelineRepository->getDefaultPipeline();

            $stage = $pipeline->stages()->first();

            $lead = $this->leadRepository->create(array_merge($rawLead, [
                'lead_pipeline_id' => $pipeline->id,
                'lead_pipeline_stage_id' => $stage->id,
            ]));

            Event::dispatch('lead.create.after', $lead);

            $leads[] = $lead;
        }

        return $leads;
    }

    public function duplicate($id)
    {
        $lead = $this->leadRepository->findOrFail($id);

        $this->preventUnauthorizedAccess($lead->user_id);

        $newLead = $lead->replicate();
        $newLead->title = 'Clone of '.$lead->title;
        $newLead->save();

        session()->flash('success', 'Lead duplicated successfully.');

        return redirect()->route('admin.leads.view', $newLead->id);
    }

    public function mergeStore($id)
    {
        $primaryLead = $this->leadRepository->findOrFail($id);

        $this->preventUnauthorizedAccess($primaryLead->user_id);

        $this->validate(request(), [
            'target_lead_id' => 'required|exists:leads,id',
            'merge_reason' => 'nullable|string',
            // Allow selecting which fields to merge. If not provided, we might default to some logic or empty array.
            'field_selections' => 'nullable|array',
        ]);

        $targetLeadId = request()->input('target_lead_id');

        if ($targetLeadId == $primaryLead->id) {
            session()->flash('error', 'Cannot merge a lead into itself.');

            return redirect()->back();
        }

        $targetLead = $this->leadRepository->findOrFail($targetLeadId);

        $this->preventUnauthorizedAccess($targetLead->user_id);

        $mergeService = app(\Webkul\Lead\Services\LeadMergeService::class);
        $fieldSelections = request()->input('field_selections', []);
        
        // Ensure some basic fields are selected if they are missing in primary
        if (empty($fieldSelections)) {
            if ((float) $primaryLead->lead_value == 0 && $targetLead->lead_value) {
                $fieldSelections['lead_value'] = $targetLead->lead_value;
            }
            if (! $primaryLead->description && $targetLead->description) {
                $fieldSelections['description'] = $targetLead->description;
            }
        }

        try {
            $mergeService->merge(
                $primaryLead->id, 
                $targetLead->id, 
                $fieldSelections, 
                auth()->check() ? auth()->id() : 1, 
                request()->input('merge_reason')
            );
            
            session()->flash('success', 'Leads merged successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error merging leads: ' . $e->getMessage());
        }

        return redirect()->route('admin.leads.view', $primaryLead->id);
    }

    /**
     * Schedule a follow-up for a lead.
     */
    public function scheduleFollowUp(int $id)
    {
        $lead = $this->leadRepository->findOrFail($id);
        $this->preventUnauthorizedAccess($lead->user_id);

        $this->validate(request(), [
            'next_action' => 'required|string',
            'date' => 'required|date',
            'owner_id' => 'nullable|exists:users,id',
        ]);

        app(LeadFollowUpService::class)->schedule(
            $lead,
            request('next_action'),
            request('date'),
            request('owner_id')
        );

        session()->flash('success', 'Follow-up scheduled successfully.');

        return redirect()->back();
    }

    /**
     * Snooze an existing follow-up.
     */
    public function snoozeFollowUp(int $id)
    {
        $lead = $this->leadRepository->findOrFail($id);
        $this->preventUnauthorizedAccess($lead->user_id);

        $this->validate(request(), [
            'date' => 'required|date',
        ]);

        app(LeadFollowUpService::class)->snooze($lead, request('date'));

        session()->flash('success', 'Follow-up snoozed successfully.');

        return redirect()->back();
    }

    /**
     * Complete the current follow-up.
     */
    public function completeFollowUp(int $id)
    {
        $lead = $this->leadRepository->findOrFail($id);
        $this->preventUnauthorizedAccess($lead->user_id);

        app(LeadFollowUpService::class)->complete($lead, request('note', ''));

        session()->flash('success', 'Follow-up completed successfully.');

        return redirect()->back();
    }

    /**
     * Stop the active nurture sequence.
     */
    public function stopNurture(int $id)
    {
        $lead = $this->leadRepository->findOrFail($id);
        $this->preventUnauthorizedAccess($lead->user_id);

        $activeEnrollment = $lead->nurtureEnrollments()->where('status', 'active')->first();
        if ($activeEnrollment) {
            $activeEnrollment->update(['status' => 'stopped']);
            session()->flash('success', 'Nurture sequence stopped successfully.');
        } else {
            session()->flash('error', 'Lead is not active in any nurture sequence.');
        }

        return redirect()->back();
    }
}
