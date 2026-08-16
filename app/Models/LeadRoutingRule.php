<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadRoutingRule extends Model
{
    protected $fillable = ['name', 'condition_type', 'condition_value', 'user_id', 'sort_order', 'status'];

    protected $casts = ['status' => 'boolean', 'sort_order' => 'integer', 'user_id' => 'integer'];
}
