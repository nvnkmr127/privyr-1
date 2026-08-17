<?php

namespace Webkul\Lead\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Lead\Contracts\StageAction as StageActionContract;

class StageAction extends Model implements StageActionContract
{
    protected $table = 'lead_pipeline_stage_actions';

    protected $fillable = [
        'lead_pipeline_stage_id',
        'type',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function stage()
    {
        return $this->belongsTo(StageProxy::modelClass(), 'lead_pipeline_stage_id');
    }
}
