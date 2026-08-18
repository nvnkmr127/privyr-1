<?php

namespace Webkul\Lead\Models;

use Carbon\Carbon;
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
use Webkul\Tag\Models\TagProxy;
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
        'status',
        'lost_reason',
        'expected_close_date',
        'closed_at',
        'user_id',
        'person_name',
        'emails',
        'contact_numbers',
        'organization_name',
        'lead_source_id',
        'lead_type_id',
        'lead_pipeline_id',
        'lead_pipeline_stage_id',
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
        'qualification_status',
        'next_action',
        'follow_up_owner_id',
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
    ];

    /**
     * The attributes that are appended.
     *
     * @var array
     */
    protected $appends = [
        'rotten_days',
    ];

    /**
     * Get the user that owns the lead.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(UserProxy::modelClass());
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
        $userId = $userId ?? auth()->guard('admin')->user()?->id;

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
                        $description = "Changed from **{$additional['old']}** to **{$additional['new']}**";
                    } else {
                        $description = json_encode($additional);
                    }
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
                    'description' => $activity->comment ?? (is_array($activity->additional) ? json_encode($activity->additional) : $activity->additional),
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

        return $events->sortByDesc('timestamp')->values();
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
        // Check for stale first (overrides score)
        if ($this->last_contacted_at && Carbon::parse($this->last_contacted_at)->diffInDays(now()) > 14) {
            return 'stale';
        }

        if ($this->last_contacted_at && Carbon::parse($this->last_contacted_at)->diffInDays(now()) > 7) {
            return 'at_risk';
        }

        if ($this->lead_score >= 80) {
            return 'hot';
        }

        if ($this->lead_score >= 50) {
            return 'warm';
        }

        return 'cold';
    }
}
