<?php

namespace Webkul\Moldable\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\Moldable\Models\WorkspaceMember;

class Workspace extends Model
{
    protected $fillable = ['name', 'slug', 'settings', 'is_active'];

    protected $casts = ['settings' => 'array', 'is_active' => 'boolean'];

    public function members(): HasMany
    {
        return $this->hasMany(WorkspaceMember::class);
    }

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    public function fields(): HasMany
    {
        return $this->hasMany(CustomField::class);
    }

    public function views(): HasMany
    {
        return $this->hasMany(SavedView::class);
    }
}
