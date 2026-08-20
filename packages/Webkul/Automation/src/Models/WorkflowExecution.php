<?php

namespace Webkul\Automation\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Automation\Contracts\WorkflowExecution as WorkflowExecutionContract;

class WorkflowExecution extends Model implements WorkflowExecutionContract
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'workflow_executions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'workflow_id',
        'entity_type',
        'entity_id',
        'trigger',
        'status',
        'error',
        'retry_count',
        'idempotency_key',
        'started_at',
        'completed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the workflow that owns the execution.
     */
    public function workflow()
    {
        return $this->belongsTo(WorkflowProxy::modelClass());
    }
}
