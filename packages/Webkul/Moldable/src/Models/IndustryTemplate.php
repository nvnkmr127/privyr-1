<?php

namespace Webkul\Moldable\Models;

use Illuminate\Database\Eloquent\Model;

class IndustryTemplate extends Model
{
    protected $fillable = ['key', 'name', 'description', 'industry', 'definition', 'is_system', 'is_active'];

    protected $casts = ['definition' => 'array', 'is_system' => 'boolean', 'is_active' => 'boolean'];
}
