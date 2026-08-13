<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DripSequenceStep extends Model
{
    protected $fillable = ['name', 'day_offset', 'content', 'is_active'];

    protected $casts = ['is_active' => 'boolean', 'day_offset' => 'integer'];
}
