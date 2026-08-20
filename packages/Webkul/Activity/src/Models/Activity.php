<?php

namespace Webkul\Activity\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Activity\Contracts\Activity as ActivityContract;
use Webkul\Lead\Models\LeadProxy;
use Webkul\User\Models\UserProxy;

class Activity extends Model implements ActivityContract
{
    /**
     * Define table name of property
     *
     * @var string
     */
    protected $table = 'activities';

    /**
     * Define relationships that should be touched on save
     *
     * @var array
     */
    protected $with = ['user'];

    /**
     * Cast attributes to date time
     *
     * @var array
     */
    protected $casts = [
        'schedule_from' => 'datetime',
        'schedule_to' => 'datetime',
        'additional' => 'array',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'type',
        'location',
        'comment',
        'additional',
        'schedule_from',
        'schedule_to',
        'is_done',
        'status',
        'outcome',
        'priority',
        'completed_at',
        'completed_by_id',
        'user_id',
        'lead_id',
    ];

    /**
     * Get the user that owns the activity.
     */
    public function user()
    {
        return $this->belongsTo(UserProxy::modelClass());
    }

    /**
     * The participants that belong to the activity.
     */
    public function participants()
    {
        return $this->hasMany(ParticipantProxy::modelClass());
    }

    /**
     * Get the file associated with the activity.
     */
    public function files()
    {
        return $this->hasMany(FileProxy::modelClass(), 'activity_id');
    }

    /**
     * Get the lead that owns the activity.
     */
    public function lead()
    {
        return $this->belongsTo(LeadProxy::modelClass());
    }

    /**
     * Get the user that completed the activity.
     */
    public function completedBy()
    {
        return $this->belongsTo(UserProxy::modelClass(), 'completed_by_id');
    }

    /**
     * Scope for pending activities.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for overdue activities.
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'pending')->where('schedule_from', '<', now());
    }

    /**
     * Scope for upcoming activities.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('status', 'pending')->where('schedule_from', '>=', now());
    }

    /**
     * Scope for completed today.
     */
    public function scopeCompletedToday($query)
    {
        return $query->where('status', 'completed')->whereDate('completed_at', today());
    }
}
