<?php

namespace Webkul\Lead\Repositories;

use App\Services\MetaConversionsApiService;
use Carbon\Carbon;
use Illuminate\Container\Container;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\Attribute\Repositories\AttributeValueRepository;
use Webkul\Core\Eloquent\Repository;
use Webkul\Lead\Contracts\Lead;
use Webkul\Lead\Services\LeadAssignmentService;
use Webkul\User\Models\UserProxy;

class LeadRepository extends Repository
{
    /**
     * Searchable fields.
     */
    protected $fieldSearchable = [
        'title',
        'lead_value',
        'status',
        'user_id',
        'user.name',
        'user.name',
        'person_name',
        'lead_source_id',
        'lead_type_id',
        'lead_pipeline_id',
        'lead_pipeline_stage_id',
        'created_at',
        'closed_at',
        'expected_close_date',
    ];

    /**
     * Create a new repository instance.
     *
     * @return void
     */
    public function __construct(
        protected StageRepository $stageRepository,
        protected AttributeRepository $attributeRepository,
        protected AttributeValueRepository $attributeValueRepository,
        Container $container
    ) {
        parent::__construct($container);
    }

    /**
     * Specify model class name.
     *
     * @return mixed
     */
    public function model()
    {
        return Lead::class;
    }

    /**
     * Get leads query.
     *
     * @param  int  $pipelineId
     * @param  int  $pipelineStageId
     * @param  string  $term
     * @param  string  $createdAtRange
     * @return mixed
     */
    public function getLeadsQuery($pipelineId, $pipelineStageId, $term, $createdAtRange)
    {
        return $this->with([
            'attribute_values',
            'pipeline',
            'stage',
        ])->scopeQuery(function ($query) use ($pipelineId, $pipelineStageId, $term, $createdAtRange) {
            return $query->select(
                'leads.id as id',
                'leads.created_at as created_at',
                'title',
                'lead_value',
                'person_name',
                'lead_pipelines.id as lead_pipeline_id',
                'lead_pipeline_stages.name as status',
                'lead_pipeline_stages.id as lead_pipeline_stage_id'
            )
                ->addSelect(DB::raw('DATEDIFF('.DB::getTablePrefix().'leads.created_at + INTERVAL lead_pipelines.rotten_days DAY, now()) as rotten_days'))
                ->leftJoin('lead_pipelines', 'leads.lead_pipeline_id', '=', 'lead_pipelines.id')
                ->leftJoin('lead_pipeline_stages', 'leads.lead_pipeline_stage_id', '=', 'lead_pipeline_stages.id')
                ->where('title', 'like', "%$term%")
                ->where('leads.lead_pipeline_id', $pipelineId)
                ->where('leads.lead_pipeline_stage_id', $pipelineStageId)
                ->when($createdAtRange, function ($query) use ($createdAtRange) {
                    return $query->whereBetween('leads.created_at', $createdAtRange);
                })
                ->where(function ($query) {
                    if ($userIds = bouncer()->getAuthorizedUserIds()) {
                        $query->whereIn('leads.user_id', $userIds);
                    }
                });
        });
    }

    /**
     * Create.
     *
     * @return Lead
     */
    public function create(array $data)
    {
        if (empty($data['expected_close_date'])) {
            $data['expected_close_date'] = null;
        }

        $lead = parent::create(array_merge([
            'lead_pipeline_id' => 1,
            'lead_pipeline_stage_id' => 1,
        ], $data));

        $this->attributeValueRepository->save(array_merge($data, [
            'entity_id' => $lead->id,
        ]));

        Event::dispatch('lead.create.after', $lead);

        if (! empty($lead->qualification_status)) {
            app(LeadQualificationRepository::class)->create([
                'lead_id' => $lead->id,
                'status' => $lead->qualification_status,
                'reason' => $data['qualification_reason'] ?? null,
                'user_id' => auth()->check() ? auth()->id() : null,
            ]);
        }

        // Assignment logic
        if (empty($lead->user_id)) {
            $lead->refresh();
            // Try to auto-assign
            $assigned = app(LeadAssignmentService::class)->assignLead($lead);

            // Fallback if no rules matched
            if (! $assigned) {
                // Determine a fallback user, e.g., super admin
                $fallbackUser = UserProxy::modelClass()::orderBy('id')->first();
                if ($fallbackUser) {
                    DB::table('leads')->where('id', $lead->id)->update(['user_id' => $fallbackUser->id]);
                    $lead->user_id = $fallbackUser->id;

                    app(LeadAssignmentRepository::class)->create([
                        'lead_id' => $lead->id,
                        'assigned_to' => $fallbackUser->id,
                        'assigned_by' => null,
                        'previous_owner' => null,
                        'reason' => 'Fallback Assignment',
                    ]);
                }
            }
        } else {
            // Manual assignment during creation
            app(LeadAssignmentRepository::class)->create([
                'lead_id' => $lead->id,
                'assigned_to' => $lead->user_id,
                'assigned_by' => auth()->check() ? auth()->id() : null,
                'previous_owner' => null,
                'reason' => $data['assignment_reason'] ?? 'Manual Assignment',
            ]);
        }

        return $lead;
    }

    /**
     * Update.
     *
     * @param  int  $id
     * @param  array|Collection  $attributes
     * @return Lead
     */
    public function update(array $data, $id, $attributes = [])
    {
        if (isset($data['lead_pipeline_stage_id'])) {
            $stage = $this->stageRepository->find($data['lead_pipeline_stage_id']);

            if (in_array($stage->code, ['won', 'lost'])) {
                $data['closed_at'] = $data['closed_at'] ?? Carbon::now();
            } else {
                $data['closed_at'] = null;
            }
        }

        if (empty($data['expected_close_date'])) {
            $data['expected_close_date'] = null;
        }

        $originalLead = $this->find($id);
        $originalUserId = $originalLead ? $originalLead->user_id : null;

        $lead = parent::update($data, $id);

        if (isset($stage) && $stage->code === 'won') {
            app(MetaConversionsApiService::class)->sendConversionEvent($lead, 'Purchase');
        }

        if ($lead->qualification_status !== ($originalLead->qualification_status ?? null)) {
            app(LeadQualificationRepository::class)->create([
                'lead_id' => $lead->id,
                'status' => $lead->qualification_status,
                'reason' => $data['qualification_reason'] ?? null,
                'user_id' => auth()->check() ? auth()->id() : null,
            ]);
        }

        if ($lead->user_id !== $originalUserId) {
            app(LeadAssignmentRepository::class)->create([
                'lead_id' => $lead->id,
                'assigned_to' => $lead->user_id,
                'assigned_by' => auth()->check() ? auth()->id() : null,
                'previous_owner' => $originalUserId,
                'reason' => $data['assignment_reason'] ?? 'Manual Reassignment',
            ]);
        }

        /**
         * If attributes are provided, only save the provided attributes and return.
         * A collection of attributes may also be provided, which will be treated as valid,
         * regardless of whether it is empty or not.
         */
        if (! empty($attributes)) {
            /**
             * If attributes are provided as an array, then fetch the attributes from the database;
             * otherwise, use the provided collection of attributes.
             */
            if (is_array($attributes)) {
                $conditions = ['entity_type' => $data['entity_type']];

                if (isset($data['quick_add'])) {
                    $conditions['quick_add'] = 1;
                }

                $attributes = $this->attributeRepository->where($conditions)
                    ->whereIn('code', $attributes)
                    ->get();
            }

            $this->attributeValueRepository->save(array_merge($data, [
                'entity_id' => $lead->id,
            ]), $attributes);

            return $lead;
        }

        $this->attributeValueRepository->save(array_merge($data, [
            'entity_id' => $lead->id,
        ]));

        return $lead;
    }

    /**
     * Get paginated leads for Unified Lead Inbox.
     *
     * @return LengthAwarePaginator
     */
    public function getInboxLeads(string $preset = 'all', ?string $search = null, array $filters = [], int $perPage = 15)
    {
        $query = $this->model->newQuery()
            ->with(['user', 'stage', 'source', 'type', 'tags']);

        // Exclude archived by default unless specifically requesting archived preset
        if ($preset === 'archived') {
            $query->archived();
        } else {
            $query->where('is_archived', false);
        }

        // Apply preset scopes
        switch ($preset) {
            case 'new':
                $query->newLeads();
                break;
            case 'unread':
                $query->unreadLeads();
                break;
            case 'my_leads':
                $query->myLeads();
                break;
            case 'unassigned':
                $query->unassignedLeads();
                break;
            case 'recently_contacted':
                $query->recentlyContacted();
                break;
            case 'follow_up_due':
                $query->followUpDue();
                break;
            case 'overdue_follow_ups':
                $query->overdueFollowUps();
                break;
            case 'stale':
                $query->staleLeads();
                break;
            case 'won':
                $query->won();
                break;
            case 'lost':
                $query->lost();
                break;
        }

        // Search query
        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('person_name', 'like', "%{$search}%")
                    ->orWhere('emails', 'like', "%{$search}%");
            });
        }

        // Additional filters
        if (! empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (! empty($filters['lead_source_id'])) {
            $query->where('lead_source_id', $filters['lead_source_id']);
        }

        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (! empty($filters['lead_pipeline_stage_id'])) {
            $query->where('lead_pipeline_stage_id', $filters['lead_pipeline_stage_id']);
        }

        return $query->latest('updated_at')->paginate($perPage);
    }

    /**
     * Get Leads that have an overdue follow-up.
     */
    public function getOverdueLeads()
    {
        return $this->model->whereNotNull('next_follow_up_at')
            ->whereDate('next_follow_up_at', '<', Carbon::today())
            ->get();
    }

    /**
     * Get Leads due for follow-up today.
     */
    public function getDueTodayLeads()
    {
        return $this->model->whereNotNull('next_follow_up_at')
            ->whereDate('next_follow_up_at', '=', Carbon::today())
            ->get();
    }

    /**
     * Get Leads that are stale (no contact for > 14 days).
     */
    public function getStaleLeads()
    {
        return $this->model->whereNotNull('last_contacted_at')
            ->where('last_contacted_at', '<', Carbon::now()->subDays(14))
            ->get();
    }

    /**
     * Get Leads that need attention (unread or no contact attempt at all).
     */
    public function getNeedsAttentionLeads()
    {
        return $this->model->where(function ($query) {
            $query->where('is_unread', true)
                ->orWhereNull('last_contacted_at');
        })->get();
    }

    /**
     * Get Leads with no next action scheduled.
     */
    public function getNoNextActionLeads()
    {
        return $this->model->whereNull('next_follow_up_at')
            ->whereNull('next_action')
            ->get();
    }
}
