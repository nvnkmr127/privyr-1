<?php

namespace Webkul\Lead\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadNurtureEnrollment extends Model
{
    protected $fillable = [
        'lead_id',
        'sequence_id',
        'current_step_id',
        'status',
        'resume_at',
        'context',
    ];

    protected $casts = [
        'resume_at' => 'datetime',
        'context' => 'array',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(LeadProxy::modelClass(), 'lead_id');
    }

    public function sequence(): BelongsTo
    {
        return $this->belongsTo(LeadNurtureSequence::class, 'sequence_id');
    }

    public function currentStep(): BelongsTo
    {
        return $this->belongsTo(LeadNurtureStep::class, 'current_step_id');
    }
}
