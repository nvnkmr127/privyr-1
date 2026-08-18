<?php

namespace Webkul\Lead\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeadNurtureSequence extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_active',
        'stop_condition',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'stop_condition' => 'array',
    ];

    public function steps(): HasMany
    {
        return $this->hasMany(LeadNurtureStep::class, 'sequence_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(LeadNurtureEnrollment::class, 'sequence_id');
    }
}
