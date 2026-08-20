<?php

namespace Webkul\Lead\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Attribute\Models\AttributeOptionProxy;
use Webkul\Lead\Contracts\LeadNurtureHistory as LeadNurtureHistoryContract;
use Webkul\User\Models\UserProxy;

class LeadNurtureHistory extends Model implements LeadNurtureHistoryContract
{
    protected $fillable = [
        'lead_id',
        'previous_status',
        'nurture_reason_id',
        'started_at',
        'expected_reengagement_date',
        'ended_at',
        'end_reason',
        'user_id',
        'notes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'expected_reengagement_date' => 'date',
        'ended_at' => 'datetime',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(LeadProxy::modelClass());
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserProxy::modelClass());
    }

    public function reason(): BelongsTo
    {
        return $this->belongsTo(AttributeOptionProxy::modelClass(), 'nurture_reason_id');
    }
}
