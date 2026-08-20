<?php

namespace Webkul\Lead\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Lead\Contracts\LeadAttributionHistory as LeadAttributionHistoryContract;

class LeadAttributionHistory extends Model implements LeadAttributionHistoryContract
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'lead_attribution_histories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'lead_id',
        'lead_source_id',
        'origin',
        'campaign',
        'medium',
        'content',
        'term',
        'landing_page',
        'form',
        'external_source',
        'external_id',
    ];

    /**
     * Get the lead that owns the history.
     */
    public function lead()
    {
        return $this->belongsTo(LeadProxy::modelClass());
    }

    /**
     * Get the source that owns the history.
     */
    public function source()
    {
        return $this->belongsTo(SourceProxy::modelClass(), 'lead_source_id');
    }
}
