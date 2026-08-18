<?php

namespace Webkul\Lead\Models;

use Illuminate\Database\Eloquent\Model;

class LeadScoreLog extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'lead_id',
        'rule_id',
        'points',
        'reason',
    ];

    /**
     * Get the lead that owns the score log.
     */
    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Get the rule that triggered the score log.
     */
    public function rule()
    {
        return $this->belongsTo(LeadScoreRule::class, 'rule_id');
    }
}
