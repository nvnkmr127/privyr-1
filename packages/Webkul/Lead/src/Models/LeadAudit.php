<?php

namespace Webkul\Lead\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Webkul\User\Models\User;

class LeadAudit extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'lead_audits';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'lead_id',
        'user_id',
        'action',
        'field',
        'old_value',
        'new_value',
        'source',
        'request_id',
    ];

    /**
     * Get the lead that owns the audit log.
     */
    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Get the user that performed the action.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Prevent updates to audit records (immutability).
     *
     * @return void
     *
     * @throws Exception
     */
    protected static function booted()
    {
        static::updating(function ($audit) {
            throw new Exception('Audit records cannot be modified.');
        });

        static::deleting(function ($audit) {
            throw new Exception('Audit records cannot be deleted.');
        });
    }
}
