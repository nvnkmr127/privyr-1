<?php

namespace Webkul\Moldable\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomField extends Model
{
    protected $fillable = [
        'workspace_id', 'entity_type', 'key', 'label', 'type', 'options', 'config',
        'sort_order', 'is_required', 'is_active', 'group_name',
    ];

    protected $casts = [
        'options' => 'array',
        'config' => 'array',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }
}
