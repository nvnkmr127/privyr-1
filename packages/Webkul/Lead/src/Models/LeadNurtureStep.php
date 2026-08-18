<?php

namespace Webkul\Lead\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadNurtureStep extends Model
{
    protected $fillable = [
        'sequence_id',
        'type',
        'config',
        'next_step_id',
        'alt_next_step_id',
    ];

    protected $casts = [
        'config' => 'array',
    ];

    public function sequence(): BelongsTo
    {
        return $this->belongsTo(LeadNurtureSequence::class, 'sequence_id');
    }

    public function nextStep(): BelongsTo
    {
        return $this->belongsTo(LeadNurtureStep::class, 'next_step_id');
    }

    public function altNextStep(): BelongsTo
    {
        return $this->belongsTo(LeadNurtureStep::class, 'alt_next_step_id');
    }
}
