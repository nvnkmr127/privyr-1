<?php

namespace Webkul\Lead\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadCaptureLog extends Model
{
    protected $table = 'lead_capture_logs';

    protected $fillable = [
        'connector_id',
        'raw_payload',
        'status',
        'lead_id',
        'error_message',
    ];

    protected $casts = [
        'raw_payload' => 'array',
    ];

    public function connector(): BelongsTo
    {
        return $this->belongsTo(LeadSourceConnector::class, 'connector_id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(LeadProxy::modelClass(), 'lead_id');
    }
}
