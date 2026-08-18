<?php

namespace Webkul\Lead\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Lead\Contracts\LeadAssignmentRuleCondition as LeadAssignmentRuleConditionContract;

class LeadAssignmentRuleCondition extends Model implements LeadAssignmentRuleConditionContract
{
    protected $table = 'lead_assignment_rule_conditions';

    protected $fillable = [
        'rule_id',
        'attribute',
        'operator',
        'value',
    ];

    public function rule()
    {
        return $this->belongsTo(LeadAssignmentRuleProxy::modelClass(), 'rule_id');
    }
}
