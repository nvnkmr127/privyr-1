<?php

namespace Webkul\Lead\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Lead\Contracts\Source as SourceContract;

class Source extends Model implements SourceContract
{
    protected $table = 'lead_sources';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'is_active',
        'default_lead_pipeline_id',
        'default_lead_pipeline_stage_id',
        'default_user_id',
    ];

    /**
     * Get the leads.
     */
    public function leads()
    {
        return $this->hasMany(LeadProxy::modelClass(), 'lead_source_id', 'id');
    }
}
