<?php

namespace Webkul\Lead\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Lead\Contracts\LeadMergeHistory as LeadMergeHistoryContract;
use Webkul\User\Models\UserProxy;

class LeadMergeHistory extends Model implements LeadMergeHistoryContract
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'lead_merge_histories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'surviving_lead_id',
        'merged_lead_id',
        'user_id',
        'merged_data',
        'merge_reason',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'merged_data' => 'array',
    ];

    /**
     * Get the surviving lead.
     */
    public function survivingLead(): BelongsTo
    {
        return $this->belongsTo(LeadProxy::modelClass(), 'surviving_lead_id');
    }

    /**
     * Get the merged lead.
     */
    public function mergedLead(): BelongsTo
    {
        return $this->belongsTo(LeadProxy::modelClass(), 'merged_lead_id');
    }

    /**
     * Get the user who performed the merge.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(UserProxy::modelClass(), 'user_id');
    }
}
