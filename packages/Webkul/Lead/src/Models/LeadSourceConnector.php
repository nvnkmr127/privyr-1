<?php

namespace Webkul\Lead\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\User\Models\UserProxy;

class LeadSourceConnector extends Model
{
    protected $table = 'lead_source_connectors';

    protected $fillable = [
        'name',
        'source_type',
        'webhook_token',
        'api_key',
        'meta_page_id',
        'meta_page_name',
        'meta_page_access_token',
        'is_active',
        'duplicate_action',
        'field_mappings',
        'embed_config',
        'default_lead_pipeline_id',
        'default_lead_pipeline_stage_id',
        'default_user_id',
        'captured_count',
        'last_received_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'field_mappings' => 'array',
        'embed_config' => 'array',
        'last_received_at' => 'datetime',
        'meta_page_access_token' => 'encrypted',
    ];

    protected $hidden = [
        'meta_page_access_token',
        'api_key',
    ];

    /**
     * Tenant scope: restrict a query to a single workspace. Callers must pass an
     * explicit workspace id resolved from the authenticated context — never from
     * raw browser input.
     */
    public function scopeForWorkspace($query, $workspaceId) {}

    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(PipelineProxy::modelClass(), 'default_lead_pipeline_id');
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(StageProxy::modelClass(), 'default_lead_pipeline_stage_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserProxy::modelClass(), 'default_user_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(LeadCaptureLog::class, 'connector_id');
    }
}
