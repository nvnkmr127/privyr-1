<?php

namespace Webkul\Lead\Providers;

use Webkul\Core\Providers\BaseModuleServiceProvider;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Models\LeadAssignment;
use Webkul\Lead\Models\LeadAssignmentRule;
use Webkul\Lead\Models\LeadAssignmentRuleCondition;
use Webkul\Lead\Models\LeadQualification;
use Webkul\Lead\Models\Pipeline;
use Webkul\Lead\Models\Source;
use Webkul\Lead\Models\Stage;
use Webkul\Lead\Models\StageAction;
use Webkul\Lead\Models\Type;
use Webkul\Lead\Models\LeadMergeHistory;
use Webkul\Lead\Models\LeadStageHistory;
use Webkul\Lead\Models\LeadStatusHistory;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        Lead::class,
        Pipeline::class,
        Source::class,
        Stage::class,
        StageAction::class,
        Type::class,
        LeadQualification::class,
        LeadAssignment::class,
        LeadAssignmentRule::class,
        LeadAssignmentRuleCondition::class,
        LeadMergeHistory::class,
        LeadStageHistory::class,
        LeadStatusHistory::class,
        \Webkul\Lead\Models\LeadAttributionHistory::class,
        \Webkul\Lead\Models\LeadNurtureHistory::class,
    ];
}
