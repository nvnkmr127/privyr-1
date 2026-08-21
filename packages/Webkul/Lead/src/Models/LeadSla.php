<?php

namespace Webkul\Lead\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Lead\Contracts\LeadSla as LeadSlaContract;

class LeadSla extends Model implements LeadSlaContract
{
    protected $table = 'lead_slas';

    protected $fillable = ['lead_id', 'sla_id', 'sla_type', 'status', 'started_at', 'due_at', 'breached_at', 'resolved_at', 'owner_id', 'team_id', 'stage_id'];

    protected $casts = ['started_at' => 'datetime', 'due_at' => 'datetime', 'breached_at' => 'datetime', 'resolved_at' => 'datetime'];
}
