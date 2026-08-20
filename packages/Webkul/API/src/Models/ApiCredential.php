<?php

namespace Webkul\API\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\API\Contracts\ApiCredential as ApiCredentialContract;

class ApiCredential extends Model implements ApiCredentialContract
{
    protected $table = 'api_credentials';

    protected $fillable = [
        'name',
        'token_hash',
        'permissions',
        'status',
        'created_by',
        'last_used_at',
        'expires_at',
    ];

    protected $casts = [
        'permissions' => 'array',
        'status' => 'boolean',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Check if the credential has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        if (empty($this->permissions)) {
            return false;
        }

        return in_array($permission, $this->permissions);
    }
}
