<?php

namespace Webkul\Lead\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Lead\Contracts\LeadStageHistory as LeadStageHistoryContract;
use Webkul\User\Models\UserProxy;

class LeadStageHistory extends Model implements LeadStageHistoryContract
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'lead_id',
        'pipeline_id',
        'previous_stage_id',
        'new_stage_id',
        'changed_by_id',
    ];

    /**
     * Get the lead that owns the history.
     */
    public function lead()
    {
        return $this->belongsTo(LeadProxy::modelClass());
    }

    /**
     * Get the pipeline.
     */
    public function pipeline()
    {
        return $this->belongsTo(PipelineProxy::modelClass());
    }

    /**
     * Get the previous stage.
     */
    public function previousStage()
    {
        return $this->belongsTo(StageProxy::modelClass(), 'previous_stage_id');
    }

    /**
     * Get the new stage.
     */
    public function newStage()
    {
        return $this->belongsTo(StageProxy::modelClass(), 'new_stage_id');
    }

    /**
     * Get the user who changed the stage.
     */
    public function changedBy()
    {
        return $this->belongsTo(UserProxy::modelClass(), 'changed_by_id');
    }
}
