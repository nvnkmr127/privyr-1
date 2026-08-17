<?php

namespace Webkul\Lead\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Lead\Contracts\Pipeline as PipelineContract;

class Pipeline extends Model implements PipelineContract
{
    protected $table = 'lead_pipelines';

    protected static function booted()
    {
        static::addGlobalScope('user_pipelines', function (\Illuminate\Database\Eloquent\Builder $builder) {
            if (app()->bound('auth') && auth()->guard('user')->check()) {
                $user = auth()->guard('user')->user();
                
                // If user is not global and has explicitly assigned pipelines, restrict to those.
                if ($user->view_permission !== 'global') {
                    $pipelineIds = $user->pipelines()->pluck('lead_pipelines.id')->toArray();
                    
                    if (!empty($pipelineIds)) {
                        $builder->whereIn('lead_pipelines.id', $pipelineIds);
                    }
                }
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'rotten_days',
        'is_default',
    ];

    /**
     * Get the leads.
     */
    public function leads()
    {
        return $this->hasMany(LeadProxy::modelClass(), 'lead_pipeline_id');
    }

    /**
     * Get the stages that owns the pipeline.
     */
    public function stages()
    {
        return $this->hasMany(StageProxy::modelClass(), 'lead_pipeline_id')->orderBy('sort_order', 'ASC');
    }

    /**
     * The users that belong to the pipeline.
     */
    public function users()
    {
        return $this->belongsToMany(\Webkul\User\Models\UserProxy::modelClass(), 'lead_pipeline_user');
    }
}
