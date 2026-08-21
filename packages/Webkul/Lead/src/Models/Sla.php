<?php

namespace Webkul\Lead\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Lead\Contracts\Sla as SlaContract;

class Sla extends Model implements SlaContract
{
    protected $table = 'slas';
    protected $fillable = ['name', 'description', 'is_active', 'assignment_sla_duration', 'first_action_sla_duration', 'follow_up_sla_duration', 'stage_sla_duration', 'source_id', 'pipeline_id', 'stage_id', 'type_id', 'team_id', 'sort_order'];
}
