<?php

namespace Webkul\Lead\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Lead\Contracts\LeadStatusHistory as LeadStatusHistoryContract;
use Webkul\User\Models\UserProxy;

class LeadStatusHistory extends Model implements LeadStatusHistoryContract
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'lead_status_histories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'lead_id',
        'previous_status',
        'new_status',
        'user_id',
        'reason',
    ];

    /**
     * Get the lead that owns the history.
     */
    public function lead()
    {
        return $this->belongsTo(LeadProxy::modelClass());
    }

    /**
     * Get the user that created the history.
     */
    public function user()
    {
        return $this->belongsTo(UserProxy::modelClass());
    }
}
