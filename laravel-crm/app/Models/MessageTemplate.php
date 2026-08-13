<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageTemplate extends Model
{
    protected $fillable = ['name', 'category', 'content', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];
}
