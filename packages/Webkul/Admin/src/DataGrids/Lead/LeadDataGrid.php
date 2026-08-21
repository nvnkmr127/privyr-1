<?php

namespace Webkul\Admin\DataGrids\Lead;

use App\Support\WorkspaceContext;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Webkul\Attribute\Models\Attribute;
use Webkul\Attribute\Models\AttributeValue;
use Webkul\Contract\Repositories\Pipeline;
use Webkul\DataGrid\DataGrid;
use Webkul\Lead\Repositories\PipelineRepository;
use Webkul\Lead\Repositories\SourceRepository;
use Webkul\Lead\Repositories\StageRepository;
use Webkul\Lead\Repositories\TypeRepository;
use Webkul\Lead\Services\LeadFilterService;
use Webkul\Lead\Services\LeadVisibilityService;
use Webkul\Tag\Repositories\TagRepository;
use Webkul\User\Repositories\GroupRepository;
use Webkul\User\Repositories\UserRepository;

class LeadDataGrid extends DataGrid
{
    /**
     * Pipeline instance.
     *
     * @var Pipeline
     */
    protected $pipeline;

    /**
     * Create data grid instance.
     *
     * @return void
     */
    public function __construct(
        protected PipelineRepository $pipelineRepository,
        protected StageRepository $stageRepository,
        protected SourceRepository $sourceRepository,
        protected TypeRepository $typeRepository,
        protected UserRepository $userRepository,
        protected TagRepository $tagRepository,
        protected LeadFilterService $leadFilterService,
    ) {
        if (request('pipeline_id')) {
            $this->pipeline = $this->pipelineRepository->find(request('pipeline_id'));
        } else {
            $this->pipeline = $this->pipelineRepository->getDefaultPipeline();
        }
    }

    /**
     * Prepare query builder.
     */
    public function prepareQueryBuilder(): Builder
    {
        $tablePrefix = DB::getTablePrefix();

        $queryBuilder = DB::table('leads')
            ->addSelect(
                'leads.id',
                'leads.title',
                'leads.status',
                'leads.data_quality_state',
                'leads.temperature',
                'leads.lead_value',
                'leads.expected_close_date',
                'leads.priority',
                'leads.location',
                'leads.lead_score',
                'leads.qualification_status',
                'leads.origin',
                'leads.campaign',
                'leads.first_origin',
                'leads.first_campaign',
                'leads.first_medium',
                'leads.first_landing_page',
                'leads.first_form',
                'leads.latest_origin',
                'leads.latest_campaign',
                'leads.ingestion_status',
                'leads.duplicate_status',
                'leads.is_merged',
                'leads.nurtured_at',
                'leads.nurture_reengagement_date',
                'leads.nurture_reason_id',
                'leads.last_activity_at',
                'leads.last_contacted_at',
                'leads.next_action',
                'leads.next_follow_up_at',
                'leads.next_action_priority',
                'leads.follow_up_owner_id',
                'leads.stage_changed_at',
                'lead_sources.name as lead_source_name',
                'first_source.name as first_source_name',
                'latest_source.name as latest_source_name',
                'lead_types.name as lead_type_name',
                'leads.created_at',
                'lead_pipeline_stages.name as stage',
                'lead_tags.tag_id as tag_id',
                'users.id as user_id',
                'users.name as sales_person',
                'groups.name as team_name',
                'leads.name as name',
                'leads.phones as phones',
                'tags.name as tag_name',
                'follow_up_owners.name as follow_up_owner_name',
                'lead_pipelines.rotten_days as pipeline_rotten_days',
                'lead_pipeline_stages.code as stage_code',
                DB::raw('CASE WHEN DATEDIFF(NOW(),'.$tablePrefix.'leads.created_at) >='.$tablePrefix.'lead_pipelines.rotten_days THEN 1 ELSE 0 END as rotten_lead'),
            )
            ->leftJoin('users', 'leads.user_id', '=', 'users.id')
            ->leftJoin('users as follow_up_owners', 'leads.follow_up_owner_id', '=', 'follow_up_owners.id')
            ->leftJoin('groups', 'leads.group_id', '=', 'groups.id')
            ->leftJoin('lead_types', 'leads.lead_type_id', '=', 'lead_types.id')
            ->leftJoin('lead_pipeline_stages', 'leads.lead_pipeline_stage_id', '=', 'lead_pipeline_stages.id')
            ->leftJoin('lead_sources', 'leads.lead_source_id', '=', 'lead_sources.id')
            ->leftJoin('lead_sources as first_source', 'leads.first_lead_source_id', '=', 'first_source.id')
            ->leftJoin('lead_sources as latest_source', 'leads.latest_lead_source_id', '=', 'latest_source.id')
            ->leftJoin('lead_pipelines', 'leads.lead_pipeline_id', '=', 'lead_pipelines.id')
            ->leftJoin('lead_tags', 'leads.id', '=', 'lead_tags.lead_id')
            ->leftJoin('tags', 'tags.id', '=', 'lead_tags.tag_id')
            ->groupBy('leads.id')
            ->where('leads.lead_pipeline_id', $this->pipeline->id)
            ->where('leads.is_archived', 0)
            ->where('leads.is_merged', 0);

        // Custom (user defined) attribute values are preloaded in formatRecords() for better performance during export.

        $user = auth()->guard('user')->user();
        if ($user) {
            $visibilityService = app(LeadVisibilityService::class);
            $visibleUserIds = $visibilityService->getVisibleUserIds($user);

            if ($visibleUserIds !== null) {
                $queryBuilder->where(function ($q) use ($visibleUserIds, $user) {
                    $q->whereIn('leads.user_id', $visibleUserIds);

                    if ($user->view_permission == 'group') {
                        $userGroupIds = $user->groups()->pluck('id')->toArray();
                        if (! empty($userGroupIds)) {
                            $q->orWhere(function ($subQ) use ($userGroupIds) {
                                $subQ->whereNull('leads.user_id')
                                    ->whereIn('leads.group_id', $userGroupIds);
                            });
                        }
                    }
                });
            }
        }
        // not apply here). Null = no current tenant / super-admin = see all.
        if (($workspaceId = app(WorkspaceContext::class)->currentWorkspaceId()) !== null) {
            $queryBuilder->where('leads.workspace_id', $workspaceId);
        }

        if (! is_null(request()->input('rotten_lead.in'))) {
            $queryBuilder->havingRaw($tablePrefix.'rotten_lead = ?', [
                (int) request()->input('rotten_lead.in'),
            ]);
        }

        $this->addFilter('id', 'leads.id');
        $this->addFilter('user', 'leads.user_id');
        $this->addFilter('sales_person', 'users.name');
        $this->addFilter('team_name', 'groups.id');
        $this->addFilter('lead_source_name', 'lead_sources.id');
        $this->addFilter('lead_type_name', 'lead_types.id');
        $this->addFilter('name', 'leads.name');
        $this->addFilter('type', 'lead_pipeline_stages.code');
        $this->addFilter('stage', 'lead_pipeline_stages.id');
        $this->addFilter('tag_name', 'tags.name');
        $this->addFilter('expected_close_date', 'leads.expected_close_date');
        $this->addFilter('created_at', 'leads.created_at');
        $this->addFilter('rotten_lead', DB::raw('DATEDIFF(NOW(), '.$tablePrefix.'leads.created_at) >= '.$tablePrefix.'lead_pipelines.rotten_days'));
        $this->addFilter('priority', 'leads.priority');
        $this->addFilter('location', 'leads.location');
        $this->addFilter('lead_score', 'leads.lead_score');
        $this->addFilter('qualification_status', 'leads.qualification_status');
        $this->addFilter('status', 'leads.status');
        $this->addFilter('temperature', 'leads.temperature');
        $this->addFilter('origin', 'leads.origin');
        $this->addFilter('campaign', 'leads.campaign');
        $this->addFilter('first_origin', 'leads.first_origin');
        $this->addFilter('first_campaign', 'leads.first_campaign');
        $this->addFilter('latest_origin', 'leads.latest_origin');
        $this->addFilter('latest_campaign', 'leads.latest_campaign');
        $this->addFilter('first_medium', 'leads.first_medium');
        $this->addFilter('first_form', 'leads.first_form');
        $this->addFilter('first_landing_page', 'leads.first_landing_page');
        $this->addFilter('first_source_name', 'first_source.id');
        $this->addFilter('latest_source_name', 'latest_source.id');
        $this->addFilter('nurtured_at', 'leads.nurtured_at');
        $this->addFilter('nurture_reengagement_date', 'leads.nurture_reengagement_date');
        $this->addFilter('ingestion_status', 'leads.ingestion_status');
        $this->addFilter('duplicate_status', 'leads.duplicate_status');
        $this->addFilter('last_activity_at', 'leads.last_activity_at');
        $this->addFilter('last_contacted_at', 'leads.last_contacted_at');
        $this->addFilter('next_follow_up_at', 'leads.next_follow_up_at');
        $this->addFilter('next_action', 'leads.next_action');
        $this->addFilter('next_action_priority', 'leads.next_action_priority');
        $this->addFilter('follow_up_owner_name', 'follow_up_owners.id');
        $this->addFilter('stage_changed_at', 'leads.stage_changed_at');
        $this->addFilter('data_quality_state', 'leads.data_quality_state');

        return $queryBuilder;
    }

    /**
     * Format records.
     */
    protected function formatRecords($records): mixed
    {
        if (request()->boolean('export') && is_iterable($records) && count($records) > 0) {
            $leadIds = [];
            foreach ($records as $record) {
                if (isset($record->id)) {
                    $leadIds[] = $record->id;
                }
            }

            if (! empty($leadIds)) {
                $attributes = $this->getCustomAttributes();
                if ($attributes->isNotEmpty()) {
                    $attributeIds = $attributes->pluck('id')->toArray();

                    $attributeValues = DB::table('attribute_values')
                        ->whereIn('entity_id', $leadIds)
                        ->where('entity_type', 'leads')
                        ->whereIn('attribute_id', $attributeIds)
                        ->get();

                    $valuesByLead = [];
                    foreach ($attributeValues as $val) {
                        $valuesByLead[$val->entity_id][$val->attribute_id] = $val;
                    }

                    foreach ($records as $record) {
                        foreach ($attributes as $attribute) {
                            $valueColumn = AttributeValue::$attributeTypeFields[$attribute->type] ?? 'text_value';
                            if (isset($valuesByLead[$record->id][$attribute->id])) {
                                $record->{$attribute->code} = $valuesByLead[$record->id][$attribute->id]->{$valueColumn};
                            } else {
                                $record->{$attribute->code} = null;
                            }
                        }
                    }
                }
            }
        }

        return parent::formatRecords($records);
    }

    /**
     * Prepare columns.
     */
    public function prepareColumns(): void
    {
        $this->addColumn([
            'index' => 'id',
            'label' => trans('admin::app.leads.index.datagrid.id'),
            'type' => 'integer',
            'sortable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'sales_person',
            'label' => trans('admin::app.leads.index.datagrid.sales-person'),
            'type' => 'string',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'searchable_dropdown',
            'filterable_options' => [
                'repository' => UserRepository::class,
                'column' => [
                    'label' => 'name',
                    'value' => 'name',
                ],
            ],
            'closure' => function ($row) {
                if (! $row->sales_person && ! $row->team_name) {
                    return '<span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">Unassigned</span>';
                }

                return $row->sales_person ?? '--';
            },
        ]);

        $this->addColumn([
            'index' => 'team_name',
            'label' => 'Team',
            'type' => 'string',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'searchable_dropdown',
            'filterable_options' => [
                'repository' => GroupRepository::class,
                'column' => [
                    'label' => 'name',
                    'value' => 'id',
                ],
            ],
            'closure' => function ($row) {
                return $row->team_name ?? '--';
            },
        ]);

        $this->addColumn([
            'index' => 'title',
            'label' => trans('admin::app.leads.index.datagrid.subject'),
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'lead_source_name',
            'label' => trans('admin::app.leads.index.datagrid.source'),
            'type' => 'string',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => $this->sourceRepository->all(['name as label', 'id as value'])->toArray(),
        ]);

        $this->addColumn([
            'index' => 'origin',
            'label' => 'Origin',
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'campaign',
            'label' => 'Campaign',
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'first_source_name',
            'label' => 'First Source',
            'type' => 'string',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => $this->sourceRepository->all(['name as label', 'id as value'])->toArray(),
            'visibility' => false,
        ]);

        $this->addColumn([
            'index' => 'latest_source_name',
            'label' => 'Latest Source',
            'type' => 'string',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => $this->sourceRepository->all(['name as label', 'id as value'])->toArray(),
            'visibility' => false,
        ]);

        $this->addColumn([
            'index' => 'first_origin',
            'label' => 'First Origin',
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
            'filterable' => true,
            'visibility' => false,
        ]);

        $this->addColumn([
            'index' => 'nurture_reengagement_date',
            'label' => 'Re-engagement Date',
            'type' => 'date',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'date_range',
            'closure' => fn ($row) => $row->nurture_reengagement_date ? core()->formatDate($row->nurture_reengagement_date) : '--',
            'visibility' => false,
        ]);

        $this->addColumn([
            'index' => 'lead_value',
            'label' => trans('admin::app.leads.index.datagrid.lead-value'),
            'type' => 'string',
            'sortable' => true,
            'searchable' => false,
            'filterable' => true,
            'closure' => fn ($row) => core()->formatBasePrice($row->lead_value, 2),
        ]);

        $this->addColumn([
            'index' => 'lead_type_name',
            'label' => trans('admin::app.leads.index.datagrid.lead-type'),
            'type' => 'string',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => $this->typeRepository->all(['name as label', 'id as value'])->toArray(),
        ]);

        $this->addColumn([
            'index' => 'tag_name',
            'label' => trans('admin::app.leads.index.datagrid.tag-name'),
            'type' => 'string',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'searchable_dropdown',
            'closure' => fn ($row) => $row->tag_name ?? '--',
            'filterable_options' => [
                'repository' => TagRepository::class,
                'column' => [
                    'label' => 'name',
                    'value' => 'name',
                ],
            ],
        ]);

        $this->addColumn([
            'index' => 'name',
            'label' => trans('admin::app.leads.index.datagrid.contact-person'),
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'stage',
            'label' => trans('admin::app.leads.index.datagrid.stage'),
            'type' => 'string',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => $this->pipeline->stages->pluck('name', 'id')
                ->map(function ($name, $id) {
                    return ['value' => $id, 'label' => $name];
                })
                ->values()
                ->all(),
        ]);

        $this->addColumn([
            'index' => 'quick_actions',
            'label' => 'One-Tap Action',
            'type' => 'string',
            'searchable' => false,
            'sortable' => false,
            'closure' => function ($row) {
                if (! $row->phones) {
                    return '--';
                }
                $numbers = json_decode($row->phones, true) ?? [];
                $phone = $numbers[0]['value'] ?? null;
                if (! $phone) {
                    return '--';
                }
                $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
                $waUrl = "https://wa.me/{$cleanPhone}?text=".rawurlencode("Hi! Regarding your inquiry: {$row->title}");

                return "<div class=\"flex items-center gap-1.5\">
                    <a href=\"{$waUrl}\" target=\"_blank\" class=\"inline-flex items-center justify-center w-7 h-7 rounded bg-emerald-600 text-white hover:bg-emerald-700 text-xs shadow-sm\" title=\"WhatsApp\"><i class=\"fa-brands fa-whatsapp\"></i></a>
                    <a href=\"tel:{$phone}\" class=\"inline-flex items-center justify-center w-7 h-7 rounded bg-blue-600 text-white hover:bg-blue-700 text-xs shadow-sm\" title=\"Call\"><i class=\"fa-solid fa-phone\"></i></a>
                </div>";
            },
        ]);

        $this->addColumn([
            'index' => 'rotten_lead',
            'label' => trans('admin::app.leads.index.datagrid.rotten-lead'),
            'type' => 'string',
            'sortable' => true,
            'searchable' => false,
            'closure' => function ($row) {
                if (! $row->rotten_lead || in_array($row->stage_code, ['won', 'lost'])) {
                    return '<span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">Fresh</span>';
                }

                return '<span class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-bold text-red-700 dark:bg-red-900/40 dark:text-red-300"><i class="fa-solid fa-fire mr-1 text-red-500"></i> Rotten</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'priority',
            'label' => 'Priority',
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
            'filterable' => true,
            'closure' => function ($row) {
                $colors = [
                    'low' => 'bg-gray-100 text-gray-800',
                    'medium' => 'bg-blue-100 text-blue-800',
                    'high' => 'bg-orange-100 text-orange-800',
                    'urgent' => 'bg-red-100 text-red-800',
                ];
                $class = $colors[$row->priority] ?? 'bg-blue-100 text-blue-800';

                return '<span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-bold uppercase tracking-wider '.$class.'">'.$row->priority.'</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'lead_score',
            'label' => 'Score',
            'type' => 'integer',
            'searchable' => false,
            'sortable' => true,
            'closure' => function ($row) {
                return '<span class="font-bold text-slate-700 dark:text-slate-300">'.($row->lead_score ?? 0).'</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'qualification_status',
            'label' => 'Qualification',
            'type' => 'string',
            'searchable' => false,
            'sortable' => true,
            'closure' => function ($row) {
                if ($row->qualification_status === 'qualified') {
                    return '<span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-bold text-emerald-800">Qualified</span>';
                } elseif ($row->qualification_status === 'disqualified') {
                    return '<span class="inline-flex items-center rounded-full bg-rose-100 px-2 py-0.5 text-xs font-bold text-rose-800">Disqualified</span>';
                }

                return '<span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">Unqualified</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'duplicate_status',
            'label' => 'Duplicate Status',
            'type' => 'string',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => [
                ['label' => 'Clean', 'value' => 'clean'],
                ['label' => 'Possible Duplicate', 'value' => 'possible_duplicate'],
                ['label' => 'Confirmed Duplicate', 'value' => 'confirmed_duplicate'],
            ],
            'closure' => function ($row) {
                if ($row->duplicate_status === 'possible_duplicate') {
                    return '<span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-bold text-amber-800"><i class="fa-solid fa-triangle-exclamation mr-1"></i> Possible</span>';
                } elseif ($row->duplicate_status === 'confirmed_duplicate') {
                    return '<span class="inline-flex items-center rounded-full bg-rose-100 px-2 py-0.5 text-xs font-bold text-rose-800">Confirmed</span>';
                }

                return '<span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">Clean</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'data_quality_state',
            'label' => 'Data Quality',
            'type' => 'string',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => [
                ['label' => 'Complete', 'value' => 'complete'],
                ['label' => 'Needs Review', 'value' => 'needs_review'],
                ['label' => 'Incomplete', 'value' => 'incomplete'],
            ],
            'closure' => function ($row) {
                if ($row->data_quality_state === 'complete') {
                    return '<span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-bold text-emerald-800">Complete</span>';
                } elseif ($row->data_quality_state === 'needs_review') {
                    return '<span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-bold text-amber-800">Needs Review</span>';
                }

                return '<span class="inline-flex items-center rounded-full bg-rose-100 px-2 py-0.5 text-xs font-medium text-rose-800">Incomplete</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'status',
            'label' => 'Status',
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => [
                ['label' => 'Open', 'value' => 'Open'],
                ['label' => 'Working', 'value' => 'Working'],
                ['label' => 'Nurturing', 'value' => 'Nurturing'],
                ['label' => 'Converted', 'value' => 'Converted'],
                ['label' => 'Lost', 'value' => 'Lost'],
                ['label' => 'Junk', 'value' => 'Junk'],
            ],
            'closure' => function ($row) {
                if ($row->status === 'Converted') {
                    return '<span class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-300">Converted</span>';
                } elseif ($row->status === 'Lost') {
                    return '<span class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900 dark:text-red-300">Lost</span>';
                } elseif ($row->status === 'Junk') {
                    return '<span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-800 dark:bg-gray-900 dark:text-gray-300">Junk</span>';
                } elseif ($row->status === 'Working') {
                    return '<span class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-300">Working</span>';
                } elseif ($row->status === 'Nurturing') {
                    return '<span class="inline-flex items-center rounded-full bg-purple-100 px-2 py-0.5 text-xs font-medium text-purple-800 dark:bg-purple-900 dark:text-purple-300">Nurturing</span>';
                }

                return '<span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-800 dark:bg-gray-900 dark:text-gray-300">Open</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'temperature',
            'label' => 'Temperature',
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => [
                ['label' => 'Cold', 'value' => 'Cold'],
                ['label' => 'Warm', 'value' => 'Warm'],
                ['label' => 'Hot', 'value' => 'Hot'],
            ],
            'closure' => function ($row) {
                if ($row->temperature === 'Hot') {
                    return '<span class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900 dark:text-red-300">Hot</span>';
                } elseif ($row->temperature === 'Warm') {
                    return '<span class="inline-flex items-center rounded-full bg-orange-100 px-2 py-0.5 text-xs font-medium text-orange-800 dark:bg-orange-900 dark:text-orange-300">Warm</span>';
                }

                return '<span class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-300">Cold</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'location',
            'label' => 'Location',
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'expected_close_date',
            'label' => trans('admin::app.leads.index.datagrid.date-to'),
            'type' => 'date',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'date_range',
            'closure' => function ($row) {
                if (! $row->expected_close_date) {
                    return '--';
                }

                return core()->formatDate($row->expected_close_date);
            },
        ]);

        $this->addColumn([
            'index' => 'created_at',
            'label' => trans('admin::app.leads.index.datagrid.created-at'),
            'type' => 'date',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'date_range',
            'closure' => fn ($row) => core()->formatDate($row->created_at),
        ]);

        $this->addColumn([
            'index' => 'last_activity_at',
            'label' => 'Last Activity',
            'type' => 'date',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'date_range',
            'closure' => function ($row) {
                if (! $row->last_activity_at) {
                    return '--';
                }

                return Carbon::parse($row->last_activity_at)->diffForHumans();
            },
        ]);

        $this->addColumn([
            'index' => 'health',
            'label' => 'Health',
            'type' => 'string',
            'searchable' => false,
            'sortable' => false,
            'filterable' => false,
            'closure' => function ($row) {
                $inactiveDays = config('lead_health.inactivity.inactive_days', 14);
                $needsAttentionDays = config('lead_health.inactivity.needs_attention_days', 7);

                if ($row->next_follow_up_at && Carbon::parse($row->next_follow_up_at)->isPast()) {
                    return '<span class="inline-flex items-center rounded-full bg-rose-100 px-2 py-0.5 text-xs font-bold text-rose-800">Overdue</span>';
                }

                $lastActivity = $row->last_activity_at ?? $row->created_at;
                if ($lastActivity) {
                    $daysSince = Carbon::parse($lastActivity)->diffInDays(now());
                    if ($daysSince >= $inactiveDays) {
                        return '<span class="inline-flex items-center rounded-full bg-gray-200 px-2 py-0.5 text-xs font-bold text-gray-700">Inactive</span>';
                    }
                    if ($daysSince >= $needsAttentionDays) {
                        return '<span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-bold text-amber-800">Needs Attention</span>';
                    }
                }

                return '<span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-bold text-emerald-800">Active</span>';
            },
        ]);
        $this->addColumn([
            'index' => 'next_action',
            'label' => 'Next Action',
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
            'filterable' => true,
            'closure' => function ($row) {
                if (! $row->next_action) {
                    return '--';
                }

                return '<span class="inline-flex items-center rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-bold text-indigo-800">'.ucfirst($row->next_action).'</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'next_follow_up_at',
            'label' => 'Next Action Date',
            'type' => 'date',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'date_range',
            'closure' => function ($row) {
                if (! $row->next_follow_up_at) {
                    return '--';
                }
                $date = Carbon::parse($row->next_follow_up_at);
                $class = $date->isPast() ? 'text-rose-600 font-bold' : 'text-slate-600';

                return '<span class="'.$class.'">'.$date->format('M d, Y h:i A').'</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'follow_up_owner_name',
            'label' => 'Next Action Owner',
            'type' => 'string',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'searchable_dropdown',
            'filterable_options' => [
                'repository' => UserRepository::class,
                'column' => [
                    'label' => 'name',
                    'value' => 'id',
                ],
            ],
            'closure' => function ($row) {
                return $row->follow_up_owner_name ?? '--';
            },
            'visibility' => false,
        ]);

        $this->addColumn([
            'index' => 'next_action_priority',
            'label' => 'Next Action Priority',
            'type' => 'string',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
            'closure' => function ($row) {
                if (! $row->next_action_priority) {
                    return '--';
                }

                return '<span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-800">'.ucfirst($row->next_action_priority).'</span>';
            },
            'visibility' => false,
        ]);

        /**
         * User defined attributes are hidden from the grid but included in the export so the data
         * entered into custom fields can be exported alongside the built-in columns.
         */
        if (request()->boolean('export')) {
            foreach ($this->getCustomAttributes() as $attribute) {
                $this->addColumn([
                    'index' => $attribute->code,
                    'label' => $attribute->name,
                    'type' => 'string',
                    'searchable' => false,
                    'sortable' => false,
                    'filterable' => false,
                    'visibility' => false,
                ]);
            }
        }
    }

    /**
     * Retrieve the user defined attributes for leads.
     */
    protected function getCustomAttributes(): Collection
    {
        return Attribute::query()
            ->where('entity_type', 'leads')
            ->where('is_user_defined', 1)
            ->get();
    }

    /**
     * Prepare actions.
     */
    public function prepareActions(): void
    {
        if (bouncer()->hasPermission('leads.view')) {
            $this->addAction([
                'icon' => 'icon-eye',
                'title' => trans('admin::app.leads.index.datagrid.view'),
                'method' => 'GET',
                'url' => fn ($row) => route('admin.leads.view', $row->id),
            ]);
        }

        if (bouncer()->hasPermission('leads.edit')) {
            $this->addAction([
                'icon' => 'icon-edit',
                'title' => trans('admin::app.leads.index.datagrid.edit'),
                'method' => 'GET',
                'url' => fn ($row) => route('admin.leads.edit', $row->id),
            ]);
        }

        if (bouncer()->hasPermission('leads.delete')) {
            $this->addAction([
                'icon' => 'icon-delete',
                'title' => trans('admin::app.leads.index.datagrid.delete'),
                'method' => 'delete',
                'url' => fn ($row) => route('admin.leads.delete', $row->id),
            ]);
        }
    }

    /**
     * Prepare mass actions.
     */
    public function prepareMassActions(): void
    {
        $bulkUrl = route('admin.leads.bulk');

        // 1. Assignment
        $users = app(UserRepository::class)->all()->map(fn ($user) => [
            'label' => 'User: '.$user->name,
            'value' => 'user_'.$user->id,
        ])->toArray();

        $groups = app(GroupRepository::class)->all()->map(fn ($group) => [
            'label' => 'Team: '.$group->name,
            'value' => 'group_'.$group->id,
        ])->toArray();

        $unassigned = [['label' => 'Unassigned', 'value' => 'unassigned']];

        $this->addMassAction([
            'title' => 'Assign',
            'url' => $bulkUrl.'?action=assign',
            'method' => 'POST',
            'options' => array_merge($unassigned, $users, $groups),
        ]);

        // 2. Lifecycle (Stage)
        $this->addMassAction([
            'title' => 'Change Stage',
            'url' => $bulkUrl.'?action=change_stage',
            'method' => 'POST',
            'options' => $this->pipeline->stages->map(fn ($stage) => [
                'label' => $stage->name,
                'value' => $stage->id,
            ])->toArray(),
        ]);

        // 3. Status
        $this->addMassAction([
            'title' => 'Change Status',
            'url' => $bulkUrl.'?action=change_status',
            'method' => 'POST',
            'options' => [
                ['label' => 'Open', 'value' => 'Open'],
                ['label' => 'Working', 'value' => 'Working'],
                ['label' => 'Converted', 'value' => 'Converted'],
                ['label' => 'Lost', 'value' => 'Lost'],
                ['label' => 'Junk', 'value' => 'Junk'],
            ],
        ]);

        // 4. Qualification
        $this->addMassAction([
            'title' => 'Qualification',
            'url' => $bulkUrl.'?action=change_qualification',
            'method' => 'POST',
            'options' => [
                ['label' => 'Qualified', 'value' => 'qualified'],
                ['label' => 'Unqualified', 'value' => 'unqualified'],
                ['label' => 'Disqualified', 'value' => 'disqualified'],
                ['label' => 'In Review', 'value' => 'in_review'],
            ],
        ]);

        // 5. Priority
        $this->addMassAction([
            'title' => 'Change Priority',
            'url' => $bulkUrl.'?action=change_priority',
            'method' => 'POST',
            'options' => [
                ['label' => 'Low', 'value' => 'low'],
                ['label' => 'Normal', 'value' => 'medium'],
                ['label' => 'High', 'value' => 'high'],
                ['label' => 'Urgent', 'value' => 'urgent'],
            ],
        ]);

        // 6. Temperature
        $this->addMassAction([
            'title' => 'Change Temperature',
            'url' => $bulkUrl.'?action=change_temperature',
            'method' => 'POST',
            'options' => [
                ['label' => 'Cold', 'value' => 'Cold'],
                ['label' => 'Warm', 'value' => 'Warm'],
                ['label' => 'Hot', 'value' => 'Hot'],
            ],
        ]);

        // 7. Add Tags
        $tags = $this->tagRepository->all()->map(fn ($t) => ['label' => $t->name, 'value' => $t->id])->toArray();
        $this->addMassAction([
            'title' => 'Add Tag',
            'url' => $bulkUrl.'?action=add_tag',
            'method' => 'POST',
            'options' => $tags,
        ]);

        // Remove Tags
        $this->addMassAction([
            'title' => 'Remove Tag',
            'url' => $bulkUrl.'?action=remove_tag',
            'method' => 'POST',
            'options' => $tags,
        ]);

        // 8. Follow-up (Will need custom modal, we use options for type if we want simple, but user asked for date/time/notes. For now we will just use a generic post and intercept it if we can, or add it to our modal blade.)
        $this->addMassAction([
            'title' => 'Create Follow-up',
            'url' => $bulkUrl.'?action=create_follow_up',
            'method' => 'POST',
            'options' => [
                ['label' => 'Call', 'value' => 'Call'],
                ['label' => 'Email', 'value' => 'Email'],
                ['label' => 'Meeting', 'value' => 'Meeting'],
            ], // Simplified for DataGrid dropdown if modal is too complex, but let's stick to this for now. We can enhance later.
        ]);

        // 9. Nurturing
        $this->addMassAction([
            'title' => 'Move to Nurturing',
            'url' => $bulkUrl.'?action=move_to_nurturing',
            'method' => 'POST',
            // Simple generic nurturing without reason for now if we don't have modal
        ]);

        // 10. Export
        $this->addMassAction([
            'title' => 'Export',
            'url' => $bulkUrl.'?action=export',
            'method' => 'POST',
            'options' => [
                ['label' => 'CSV', 'value' => 'csv'],
                ['label' => 'Excel', 'value' => 'xlsx'],
            ],
        ]);

        // 11. Delete
        $this->addMassAction([
            'icon' => 'icon-delete',
            'title' => trans('admin::app.leads.index.datagrid.mass-delete'),
            'method' => 'POST',
            'url' => $bulkUrl.'?action=delete',
        ]);

        // Add Custom EAV Attributes as Filterable Columns
        $customAttributes = Attribute::where('entity_type', 'leads')->get();
        foreach ($customAttributes as $attribute) {
            $this->addColumn([
                'index' => $attribute->code,
                'label' => $attribute->name,
                'type' => $attribute->type == 'date' || $attribute->type == 'datetime' ? $attribute->type : 'string',
                'searchable' => false,
                'sortable' => false,
                'filterable' => true,
                'visibility' => false,
            ]);
        }
    }

    /**
     * Override processRequestedFilters to support Advanced Filters and ANY/ALL groups
     */
    protected function processRequestedFilters(array $requestedFilters)
    {
        $matchType = request('match_type') === 'any' ? 'any' : 'all';

        // Extract available columns as an array of definitions for the service
        $availableColumns = collect($this->columns)->map(function ($column) {
            return method_exists($column, 'toArray') ? $column->toArray() : (array) $column;
        })->all();

        $this->queryBuilder = $this->leadFilterService->applyAdvancedFilters(
            $this->queryBuilder,
            $requestedFilters,
            $matchType,
            $availableColumns
        );

        return $this->queryBuilder;
    }
}
