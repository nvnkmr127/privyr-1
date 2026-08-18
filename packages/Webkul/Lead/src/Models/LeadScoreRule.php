<?php

namespace Webkul\Lead\Models;

use Illuminate\Database\Eloquent\Model;

class LeadScoreRule extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'type',
        'conditions',
        'points',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'conditions' => 'json',
        'is_active' => 'boolean',
    ];
}
