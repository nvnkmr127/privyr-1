<?php

namespace Webkul\Lead\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Webkul\Activity\Models\ActivityProxy;
use Webkul\Activity\Traits\LogsActivity;
use Webkul\Attribute\Traits\CustomAttribute;
use Webkul\Email\Models\EmailProxy;
use Webkul\Lead\Contracts\Lead as LeadContract;
use Webkul\Lead\Services\LeadVisibilityService;
use Webkul\Tag\Models\TagProxy;
use Webkul\User\Models\GroupProxy;
use Webkul\User\Models\User;
use Webkul\User\Models\UserProxy;

class Lead extends Model implements LeadContract
{
    use CustomAttribute, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'description',
        'lead_value',
        'lost_reason',
        'expected_close_date',
        'closed_at',
        'person_name',
        'emails',
        'contact_numbers',
        'organization_name',
        'lead_source_id',
        'lead_type_id',
        'lead_pipeline_id',
        'priority',
        'is_unread',
        'last_contacted_at',
        'next_follow_up_at',
        'is_archived',
        'lead_score',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'location',
        'next_action',
        'follow_up_owner_id',
        'first_lead_source_id',
        'first_origin',
        'first_campaign',
        'first_medium',
        'first_content',
        'first_term',
        'first_landing_page',
        'first_form',
        'first_external_source',
        'first_external_id',
        'latest_lead_source_id',
        'latest_origin',
        'latest_campaign',
        'latest_medium',
        'latest_content',
        'latest_term',
        'latest_landing_page',
        'latest_form',
        'latest_external_source',
        'latest_external_id',
        'origin',
        'external_id',
        'ingestion_status',
        'campaign',
        'normalized_primary_email',
        'normalized_primary_phone',
        'duplicate_status',
        'duplicate_of_id',
        'is_merged',
        'merged_into_id',
        'last_activity_at',
        'stage_changed_at',
        'temperature',
        'junk_reason',
        'converted_at',
        'converted_by',
        'nurture_reason_id',
        'nurtured_at',
        'nurture_reengagement_date',
        'nurture_notes',
    ];

    /**
     * Cast the attributes to their respective types.
     *
     * @var array
     */
    protected $casts = [
        'closed_at' => 'datetime:D M d, Y H:i A',
        'expected_close_date' => 'date:D M d, Y',
        'is_unread' => 'boolean',
        'is_archived' => 'boolean',
        'last_contacted_at' => 'datetime',
        'next_follow_up_at' => 'datetime',
        'emails' => 'array',
        'contact_numbers' => 'array',
        'is_merged' => 'boolean',
        'last_activity_at' => 'datetime',
        'stage_changed_at' => 'datetime',
        'converted_at' => 'datetime:D M d, Y H:i A',
        'nurtured_at' => 'datetime',
        'nurture_reengagement_date' => 'date:Y-m-d',
    ];

    /**
     * The attributes that are appended.
     *
     * @var array
     */
    protected $appends = [
        'rotten_days',
        'lead_age_days',
        'stage_age_days',
    ];

    /**
     * Get the user that owns the lead.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(UserProxy::modelClass());
    }

    /**
     * Get the group/team that owns the lead.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(GroupProxy::modelClass());
    }

    /**
     * Get the user responsible for the next follow-up.
     */
    public function followUpOwner(): BelongsTo
    {
        return $this->belongsTo(UserProxy::modelClass(), 'follow_up_owner_id');
    }

    /**
     * Compute follow-up state dynamically
     */
    public function getFollowUpStateAttribute(): string
    {
        if ($this->next_follow_up_at) {
            if ($this->next_follow_up_at->isPast() && ! $this->next_follow_up_at->isToday()) {
                return 'Overdue';
            }
            if ($this->next_follow_up_at->isToday()) {
                return 'Due Today';
            }

            return 'Upcoming';
        }

        if ($this->is_unread || is_null($this->last_contacted_at)) {
            return 'Needs Attention';
        }

        if ($this->last_contacted_at && $this->last_contacted_at->diffInDays(Carbon::now()) > 14) {
            return 'Stale';
        }

        return 'No Next Action';
    }

    /**
     * Get the type that owns the lead.
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(TypeProxy::modelClass(), 'lead_type_id');
    }

    /**
     * Get the source that owns the lead.
     */
    public function source(): BelongsTo
    {
        return $this->belongsTo(SourceProxy::modelClass(), 'lead_source_id');
    }

    /**
     * Get the nurture enrollments for this lead.
     */
    public function nurtureEnrollments(): HasMany
    {
        return $this->hasMany(LeadNurtureEnrollment::class, 'lead_id');
    }

    /**
     * Get active nurture status.
     */
    public function getActiveNurtureStatusAttribute(): ?string
    {
        $activeEnrollment = $this->nurtureEnrollments()->where('status', 'active')->first();
        if ($activeEnrollment) {
            return 'Active in: '.($activeEnrollment->sequence->name ?? 'Sequence');
        }

        return null;
    }

    /**
     * Get the pipeline that owns the lead.
     */
    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(PipelineProxy::modelClass(), 'lead_pipeline_id');
    }

    /**
     * Get the pipeline stage that owns the lead.
     */
    public function stage(): BelongsTo
    {
        return $this->belongsTo(StageProxy::modelClass(), 'lead_pipeline_stage_id');
    }

    /**
     * Get the activities.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(ActivityProxy::modelClass());
    }

    /**
     * Get the qualifications.
     */
    public function qualifications(): HasMany
    {
        return $this->hasMany(LeadQualificationProxy::modelClass());
    }

    /**
     * Get the assignments.
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(LeadAssignmentProxy::modelClass());
    }

    /**
     * Get the latest qualification.
     */
    public function latestQualification()
    {
        return $this->hasOne(LeadQualificationProxy::modelClass())->latestOfMany();
    }

    /**
     * Get the emails.
     */
    public function emails(): HasMany
    {
        return $this->hasMany(EmailProxy::modelClass());
    }

    /**
     * The tags that belong to the lead.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(TagProxy::modelClass(), 'lead_tags');
    }

    /**
     * Returns the rotten days
     */
    public function getRottenDaysAttribute()
    {
        if (! $this->stage) {
            return 0;
        }

        if (in_array($this->stage->code, ['won', 'lost'])) {
            return 0;
        }

        if (! $this->created_at) {
            return 0;
        }

        $rottenDate = $this->created_at->addDays($this->pipeline->rotten_days);

        return $rottenDate->diffInDays(Carbon::now(), false);
    }

    /**
     * Get the lead age in days
     */
    public function getLeadAgeDaysAttribute()
    {
        if (! $this->created_at) {
            return 0;
        }

        return $this->created_at->diffInDays(Carbon::now());
    }

    /**
     * Get the stage age in days
     */
    public function getStageAgeDaysAttribute()
    {
        if (! $this->stage_changed_at) {
            return $this->getLeadAgeDaysAttribute();
        }

        return $this->stage_changed_at->diffInDays(Carbon::now());
    }

    /**
     * Get the next follow up activity.
     */
    public function getNextFollowUpAttribute()
    {
        return $this->activities()->where('status', 'pending')->where('schedule_from', '>=', now())->orderBy('schedule_from', 'asc')->first();
    }

    /**
     * Get the last contacted activity.
     */
    public function getLastContactedAttribute()
    {
        return $this->activities()->where('status', 'completed')->orderBy('completed_at', 'desc')->first();
    }

    /**
     * Get the count of pending follow-ups.
     */
    public function getFollowUpCountAttribute()
    {
        return $this->activities()->where('status', 'pending')->count();
    }

    /**
     * Get the count of completed follow-ups.
     */
    public function getCompletedFollowUpsAttribute()
    {
        return $this->activities()->where('status', 'completed')->count();
    }

    /**
     * Get the count of overdue follow-ups.
     */
    public function getOverdueFollowUpsAttribute()
    {
        return $this->activities()->where('status', 'pending')->where('schedule_from', '<', now())->count();
    }

    /**
     * Query scope for New Leads.
     */
    public function scopeNewLeads($query)
    {
        return $query->where('created_at', '>=', Carbon::now()->subDays(7));
    }

    /**
     * Query scope for Unread Leads.
     */
    public function scopeUnreadLeads($query)
    {
        return $query->where('is_unread', true);
    }

    /**
     * Query scope for My Leads.
     */
    public function scopeMyLeads($query, $userId = null)
    {
        $userId = $userId ?? auth()->guard('user')->user()?->id;

        return $query->where('user_id', $userId);
    }

    /**
     * Query scope for Unassigned Leads.
     */
    public function scopeUnassignedLeads($query)
    {
        return $query->whereNull('user_id');
    }

    /**
     * Query scope for Recently Contacted Leads.
     */
    public function scopeRecentlyContacted($query)
    {
        return $query->whereNotNull('last_contacted_at')->orderBy('last_contacted_at', 'desc');
    }

    /**
     * Query scope for Follow Up Due Leads.
     */
    public function scopeFollowUpDue($query)
    {
        return $query->whereNotNull('next_follow_up_at')->whereDate('next_follow_up_at', '=', Carbon::today());
    }

    /**
     * Query scope for Overdue Follow Ups.
     */
    public function scopeOverdueFollowUps($query)
    {
        return $query->whereNotNull('next_follow_up_at')->where('next_follow_up_at', '<', Carbon::now());
    }

    /**
     * Query scope for Stale Leads.
     */
    public function scopeStaleLeads($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('last_contacted_at')
                ->where('created_at', '<=', Carbon::now()->subDays(14))
                ->orWhere('last_contacted_at', '<=', Carbon::now()->subDays(14));
        });
    }

    /**
     * Query scope for Won Leads.
     */
    public function scopeWon($query)
    {
        return $query->whereHas('stage', fn ($q) => $q->where('code', 'won'));
    }

    /**
     * Query scope for Lost Leads.
     */
    public function scopeLost($query)
    {
        return $query->whereHas('stage', fn ($q) => $q->where('code', 'lost'));
    }

    /**
     * Query scope for Archived Leads.
     */
    public function scopeArchived($query)
    {
        return $query->where('is_archived', true);
    }

    /**
     * Query scope for Lead Visibility.
     * Enforces that the queried leads are accessible to the given user based on their visibility scope.
     *
     * @param  Builder  $query
     * @param  User|null  $user
     */
    public function scopeVisibleTo($query, $user = null, string $action = 'view')
    {
        $user = $user ?? auth()->guard('user')->user();
        if (! $user) {
            return $query->whereRaw('1 = 0'); // Deny if no user
        }

        $visibilityService = app(LeadVisibilityService::class);
        $visibleUserIds = $visibilityService->getVisibleUserIds($user, $action);

        if ($visibleUserIds === null) {
            // Global access, no filter needed on user_id
            return $query;
        }

        return $query->where(function ($q) use ($visibleUserIds, $user) {
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

    /**
     * Retrieve all lead events formatted for chronological activity timeline.
     *
     * @return Collection
     */
    public function getChronologicalTimeline()
    {
        $events = collect();

        // 1. Lead Created Event
        if ($this->created_at) {
            $events->push([
                'id' => 'created_'.$this->id,
                'type' => 'lead_created',
                'title' => 'Lead Created',
                'description' => 'Lead "'.$this->title.'" was created.',
                'icon' => 'icon-add',
                'badge_color' => 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300',
                'timestamp' => $this->created_at,
            ]);
        }

        // 2. Activities (Calls, Notes, Tasks, Emails, Meetings, System)
        foreach ($this->activities as $activity) {
            $type = $activity->type ?? 'note';
            $iconMap = [
                'call' => 'icon-phone',
                'meeting' => 'icon-calendar',
                'note' => 'icon-note',
                'task' => 'icon-task',
                'email' => 'icon-mail',
                'whatsapp' => 'icon-message',
                'system' => 'icon-activity',
            ];

            if ($type === 'system') {
                $additional = $activity->additional;
                $description = '';
                if (is_array($additional)) {
                    if (isset($additional['old']) && isset($additional['new'])) {
                        $oldVal = is_array($additional['old']) ? json_encode($additional['old']) : $additional['old'];
                        $newVal = is_array($additional['new']) ? json_encode($additional['new']) : $additional['new'];
                        $description = "Changed from **{$oldVal}** to **{$newVal}**";
                    } else {
                        $description = json_encode($additional);
                    }
                } else {
                    $description = (string) $additional;
                }

                $events->push([
                    'id' => 'activity_'.$activity->id,
                    'type' => 'activity_system',
                    'title' => $activity->title ?? 'System Event',
                    'description' => $description,
                    'icon' => 'icon-activity',
                    'badge_color' => 'bg-slate-100 text-slate-800 dark:bg-slate-900 dark:text-slate-300',
                    'timestamp' => $activity->created_at ?? $this->created_at,
                ]);
            } else {
                $events->push([
                    'id' => 'activity_'.$activity->id,
                    'type' => 'activity_'.$type,
                    'title' => ucfirst($type).' '.($activity->title ? ': '.$activity->title : ''),
                    'description' => ($activity->comment ?? (is_array($activity->additional) ? json_encode($activity->additional) : $activity->additional)).($activity->outcome ? "\n\n**Outcome:** ".$activity->outcome : ''),
                    'icon' => $iconMap[$type] ?? 'icon-note',
                    'badge_color' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300',
                    'timestamp' => $activity->created_at ?? $this->created_at,
                ]);
            }
        }

        // 3. Emails Sent / Opened
        foreach ($this->emails()->get() as $email) {
            $events->push([
                'id' => 'email_'.$email->id,
                'type' => 'email_sent',
                'title' => 'Email Sent: '.($email->subject ?? 'Direct Email'),
                'description' => Str::limit(strip_tags($email->reply ?? $email->name ?? ''), 120),
                'icon' => 'icon-mail',
                'badge_color' => 'bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300',
                'timestamp' => $email->created_at,
            ]);
        }

        // 4. Follow-up Scheduled Event
        if ($this->next_follow_up_at) {
            $events->push([
                'id' => 'followup_'.$this->id,
                'type' => 'follow_up_scheduled',
                'title' => 'Follow-up Scheduled',
                'description' => 'Next follow-up date set for '.$this->next_follow_up_at->format('M d, Y h:i A'),
                'icon' => 'icon-calendar',
                'badge_color' => 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
                'timestamp' => $this->next_follow_up_at,
            ]);
        }

        // 5. Status Histories
        foreach ($this->statusHistories as $history) {
            $events->push([
                'id' => 'status_'.$history->id,
                'type' => 'status_changed',
                'title' => 'Status Changed',
                'description' => 'Status changed from '.($history->previous_status ?? 'Unknown').' to '.$history->new_status.($history->reason ? ' ('.$history->reason.')' : ''),
                'icon' => 'icon-activity',
                'badge_color' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300',
                'timestamp' => $history->created_at,
            ]);
        }

        return $events->sortByDesc('timestamp')->values();
    }

    public function assign(?int $userId, ?int $groupId = null): self
    {
        $this->user_id = $userId;
        if (func_num_args() > 1) {
            $this->group_id = $groupId;
        }
        $this->saveQuietly();

        return $this;
    }

    public function changeStage(int $stageId): self
    {
        $oldStageId = $this->lead_pipeline_stage_id;
        if ((int) $oldStageId !== $stageId) {
            $this->lead_pipeline_stage_id = $stageId;
            $this->stage_changed_at = Carbon::now();
            $this->save();

            $this->stageHistories()->create([
                'previous_stage_id' => $oldStageId,
                'new_stage_id' => $stageId,
            ]);
        }

        return $this;
    }

    public function transitionStatus(string $status, ?string $reason = null): self
    {
        $oldStatus = $this->status;
        if ($oldStatus !== $status) {
            $this->status = $status;

            if ($status === 'Closed' || in_array(strtolower($status), ['won', 'lost'])) {
                $this->closed_at = Carbon::now();
            }

            $this->save();

            $this->statusHistories()->create([
                'previous_status' => $oldStatus,
                'new_status' => $status,
                'reason' => $reason,
            ]);
        }

        return $this;
    }

    public function changeQualificationStatus(?string $status): self
    {
        $this->qualification_status = $status;
        $this->save();

        return $this;
    }

    public static function boot()
    {
        parent::boot();
    }

    /**
     * Get the score logs for the lead.
     */
    public function scoreLogs()
    {
        return $this->hasMany(LeadScoreLog::class);
    }

    /**
     * Get the health state attribute.
     */
    public function getHealthStateAttribute()
    {
        // Explicitly handle Nurturing status
        if ($this->status === 'Nurturing') {
            if ($this->nurture_reengagement_date && $this->nurture_reengagement_date->isPast() && ! $this->nurture_reengagement_date->isToday()) {
                return 'Overdue'; // Re-engagement date passed
            }

            return 'Nurturing'; // Safely nurturing, ignore inactivity
        }

        $inactiveDays = config('lead_health.inactivity.inactive_days', 14);
        $needsAttentionDays = config('lead_health.inactivity.needs_attention_days', 7);

        // Check for overdue follow-up
        if ($this->next_follow_up_at && $this->next_follow_up_at->isPast()) {
            return 'Overdue';
        }

        // Check for inactivity
        $lastActivity = $this->last_activity_at ?? $this->created_at;

        if ($lastActivity) {
            $daysSinceActivity = Carbon::parse($lastActivity)->diffInDays(now());

            if ($daysSinceActivity >= $inactiveDays) {
                return 'Inactive';
            }

            if ($daysSinceActivity >= $needsAttentionDays) {
                return 'Needs Attention';
            }
        }

        // Active if no bad health conditions met
        return 'Active';
    }

    /**
     * Get the lead this one is a duplicate of.
     */
    public function duplicateOf(): BelongsTo
    {
        return $this->belongsTo(self::class, 'duplicate_of_id');
    }

    /**
     * Get the lead this one was merged into.
     */
    public function mergedInto(): BelongsTo
    {
        return $this->belongsTo(self::class, 'merged_into_id');
    }

    /**
     * Get the merge histories for this lead (where it is the surviving lead).
     */
    public function mergeHistories(): HasMany
    {
        return $this->hasMany(LeadMergeHistoryProxy::modelClass(), 'surviving_lead_id');
    }

    /**
     * Query scope to exclude merged leads.
     */
    public function scopeActive($query)
    {
        return $query->where('is_merged', false);
    }

    /**
     * Get the stage histories for the lead.
     */
    public function stageHistories(): HasMany
    {
        return $this->hasMany(LeadStageHistoryProxy::modelClass(), 'lead_id')->orderBy('created_at', 'desc');
    }

    /**
     * Get the status histories for the lead.
     */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(LeadStatusHistoryProxy::modelClass(), 'lead_id')->orderBy('created_at', 'desc');
    }

    /**
     * Get the user that converted the lead.
     */
    public function convertedBy(): BelongsTo
    {
        return $this->belongsTo(UserProxy::modelClass(), 'converted_by');
    }

    /**
     * Get the first source that generated the lead.
     */
    public function firstSource(): BelongsTo
    {
        return $this->belongsTo(SourceProxy::modelClass(), 'first_lead_source_id');
    }

    /**
     * Get the latest source that generated/updated the lead.
     */
    public function latestSource(): BelongsTo
    {
        return $this->belongsTo(SourceProxy::modelClass(), 'latest_lead_source_id');
    }

    /**
     * Get the attribution histories for the lead.
     */
    public function attributionHistories(): HasMany
    {
        return $this->hasMany(LeadAttributionHistoryProxy::modelClass(), 'lead_id')->orderBy('created_at', 'desc');
    }

    /**
     * Get the nurture histories for the lead.
     */
    public function nurtureHistories(): HasMany
    {
        return $this->hasMany(LeadNurtureHistoryProxy::modelClass(), 'lead_id')->orderBy('created_at', 'desc');
    }

    /**
     * Get the SLAs for the lead.
     */
    public function slas(): HasMany
    {
        return $this->hasMany(LeadSlaProxy::modelClass(), 'lead_id');
    }
}
