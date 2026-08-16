<?php

namespace Webkul\Moldable\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FieldGroup extends Model
{
    protected $table = 'moldable_field_groups';

    protected $fillable = [
        'workspace_id',
        'entity_type',
        'name',
        'slug',
        'sort_order',
    ];

    protected $casts = [
        'workspace_id' => 'integer',
        'sort_order' => 'integer',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function groupAttributes(): HasMany
    {
        return $this->hasMany(FieldGroupAttribute::class, 'group_id')->orderBy('sort_order');
    }
}
