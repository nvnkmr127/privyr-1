<?php

namespace Webkul\Lead\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Lead\Contracts\LeadAssignmentRule as LeadAssignmentRuleContract;
use Webkul\User\Models\GroupProxy;
use Webkul\User\Models\UserProxy;

class LeadAssignmentRule extends Model implements LeadAssignmentRuleContract
{
    protected $table = 'lead_assignment_rules';

    protected $fillable = [
        'name',
        'type',
        'status',
        'sort_order',
        'fallback_type',
        'fallback_user_id',
        'fallback_group_id',
    ];

    public function conditions()
    {
        return $this->hasMany(LeadAssignmentRuleConditionProxy::modelClass(), 'rule_id');
    }

    public function users()
    {
        return $this->belongsToMany(UserProxy::modelClass(), 'lead_assignment_rule_users', 'rule_id', 'user_id')
            ->withPivot('weight', 'last_assigned_at', 'capacity');
    }

    public function groups()
    {
        return $this->belongsToMany(GroupProxy::modelClass(), 'lead_assignment_rule_groups', 'rule_id', 'group_id');
    }
}
