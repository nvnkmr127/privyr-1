<?php

namespace Webkul\Lead\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Activity\Repositories\ActivityRepository;
use Webkul\Lead\Contracts\LeadAssignment as LeadAssignmentContract;
use Webkul\User\Models\GroupProxy;
use Webkul\User\Models\UserProxy;

class LeadAssignment extends Model implements LeadAssignmentContract
{
    protected $table = 'lead_assignments';

    protected $fillable = [
        'lead_id',
        'assigned_to',
        'assigned_by',
        'previous_owner',
        'reason',
        'previous_group_id',
        'assigned_group_id',
    ];

    public function lead()
    {
        return $this->belongsTo(LeadProxy::modelClass());
    }

    public function assignedTo()
    {
        return $this->belongsTo(UserProxy::modelClass(), 'assigned_to');
    }

    public function assignedBy()
    {
        return $this->belongsTo(UserProxy::modelClass(), 'assigned_by');
    }

    public function previousOwner()
    {
        return $this->belongsTo(UserProxy::modelClass(), 'previous_owner');
    }

    public function assignedGroup()
    {
        return $this->belongsTo(GroupProxy::modelClass(), 'assigned_group_id');
    }

    public function previousGroup()
    {
        return $this->belongsTo(GroupProxy::modelClass(), 'previous_group_id');
    }

    protected static function booted()
    {
        static::created(function ($model) {
            $activityRepo = app(ActivityRepository::class);

            $assignedToName = $model->assignedTo ? $model->assignedTo->name : ($model->assignedGroup ? $model->assignedGroup->name.' (Team)' : 'System');
            $assignedByName = $model->assignedBy ? $model->assignedBy->name : 'System';

            $title = "Lead assigned to {$assignedToName} by {$assignedByName}";

            $activityRepo->create([
                'type' => 'system',
                'title' => $title,
                'is_done' => 1,
                'lead_id' => $model->lead_id,
                'user_id' => $model->assigned_by ?? $model->assigned_to,
                'comment' => $model->reason,
            ]);
        });
    }
}
