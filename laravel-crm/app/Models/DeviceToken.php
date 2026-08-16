<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\User\Models\UserProxy;

class DeviceToken extends Model
{
    protected $fillable = ['user_id', 'token', 'platform', 'device_name', 'last_used_at'];

    protected $casts = [
        'user_id' => 'integer',
        'last_used_at' => 'datetime',
    ];

    /**
     * The agent (Krayin admin user) this device belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(UserProxy::modelClass(), 'user_id');
    }
}
