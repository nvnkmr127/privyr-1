<?php

namespace Webkul\Moldable\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    protected $fillable = ['workspace_id', 'name', 'slug', 'description', 'settings', 'is_active'];

    protected $casts = ['settings' => 'array', 'is_active' => 'boolean'];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(WorkspaceMember::class);
    }
}
