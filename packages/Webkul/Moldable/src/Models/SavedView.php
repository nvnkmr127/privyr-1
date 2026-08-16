<?php

namespace Webkul\Moldable\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedView extends Model
{
    protected $table = 'moldable_saved_views';

    protected $fillable = [
        'workspace_id', 'user_id', 'name', 'entity_type', 'filters', 'columns',
        'sort', 'group_by', 'visibility', 'is_default',
    ];

    protected $casts = [
        'filters' => 'array',
        'columns' => 'array',
        'sort' => 'array',
        'group_by' => 'array',
        'is_default' => 'boolean',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }
}
