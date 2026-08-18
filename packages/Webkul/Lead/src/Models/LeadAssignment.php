<?php

namespace Webkul\Lead\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Lead\Contracts\LeadAssignment as LeadAssignmentContract;
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

    protected static function booted()
    {
        static::created(function ($model) {
            $activityRepo = app(\Webkul\Activity\Repositories\ActivityRepository::class);
            
            $assignedToName = $model->assignedTo ? $model->assignedTo->name : 'System';
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
