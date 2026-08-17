<?php

namespace Webkul\Lead\Models;

use App\Support\Concerns\BelongsToWorkspace;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Webkul\Activity\Models\ActivityProxy;
use Webkul\Activity\Traits\LogsActivity;
use Webkul\Attribute\Traits\CustomAttribute;
use Webkul\Contact\Models\PersonProxy;
use Webkul\Email\Models\EmailProxy;
use Webkul\Lead\Contracts\Lead as LeadContract;
use Webkul\Quote\Models\QuoteProxy;
use Webkul\Tag\Models\TagProxy;
use Webkul\User\Models\UserProxy;

class Lead extends Model implements LeadContract
{
    use BelongsToWorkspace, CustomAttribute, LogsActivity;

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
        'person_id',
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
        'is_qualified',
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
        'is_qualified' => 'boolean',
        'last_contacted_at' => 'datetime',
        'next_follow_up_at' => 'datetime',
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
     * Get the person that owns the lead.
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(PersonProxy::modelClass());
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
    public function activities(): BelongsToMany
    {
        return $this->belongsToMany(ActivityProxy::modelClass(), 'lead_activities');
    }

    /**
     * Get the products.
     */
    public function products(): HasMany
    {
        return $this->hasMany(ProductProxy::modelClass());
    }

    /**
     * Get the emails.
     */
    public function emails(): HasMany
    {
        return $this->hasMany(EmailProxy::modelClass());
    }

    /**
     * The quotes that belong to the lead.
     */
    public function quotes(): BelongsToMany
    {
        return $this->belongsToMany(QuoteProxy::modelClass(), 'lead_quotes');
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

        // 2. Activities (Calls, Notes, Tasks, Emails, Meetings)
        foreach ($this->activities as $activity) {
            $type = $activity->type ?? 'note';
            $iconMap = [
                'call' => 'icon-phone',
                'meeting' => 'icon-calendar',
                'note' => 'icon-note',
                'task' => 'icon-task',
                'email' => 'icon-mail',
                'whatsapp' => 'icon-message',
            ];

            $events->push([
                'id' => 'activity_'.$activity->id,
                'type' => 'activity_'.$type,
                'title' => ucfirst($type).' '.($activity->title ? ': '.$activity->title : ''),
                'description' => $activity->comment ?? $activity->additional,
                'icon' => $iconMap[$type] ?? 'icon-note',
                'badge_color' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300',
                'timestamp' => $activity->created_at ?? $this->created_at,
            ]);
        }

        // 3. Emails Sent / Opened
        foreach ($this->emails as $email) {
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

        static::saving(function ($lead) {
            $lead->lead_score = $lead->calculateScore();
        });
    }

    public function calculateScore()
    {
        $score = 0;
        if ($this->person) {
            $score += 20;
            if ($this->person->emails && count($this->person->emails) > 0) {
                $score += 10;
            }
            if ($this->person->contact_numbers && count($this->person->contact_numbers) > 0) {
                $score += 10;
            }
        }
        if ($this->lead_value > 0) {
            $score += 20;
        }
        if ($this->priority === 'urgent') {
            $score += 20;
        } elseif ($this->priority === 'high') {
            $score += 15;
        } elseif ($this->priority === 'medium') {
            $score += 10;
        } else {
            $score += 5;
        }
        if ($this->activities()->count() > 0) {
            $score += 20;
        }
        if ($this->is_qualified) {
            $score += 20;
        }

        return min($score, 100);
    }
}
