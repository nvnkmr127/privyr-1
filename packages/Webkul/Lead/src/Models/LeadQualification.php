<?php

namespace Webkul\Lead\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Activity\Repositories\ActivityRepository;
use Webkul\Lead\Contracts\LeadQualification as LeadQualificationContract;
use Webkul\User\Models\UserProxy;

class LeadQualification extends Model implements LeadQualificationContract
{
    protected $table = 'lead_qualifications';

    protected $fillable = [
        'lead_id',
        'user_id',
        'status',
        'reason',
    ];

    public function lead()
    {
        return $this->belongsTo(LeadProxy::modelClass());
    }

    public function user()
    {
        return $this->belongsTo(UserProxy::modelClass());
    }

    protected static function booted()
    {
        static::created(function ($model) {
            $activityRepo = app(ActivityRepository::class);
            $statusLabel = ucfirst($model->status);
            $title = trans('admin::app.activities.qualification-updated', ['status' => $statusLabel]);

            // Provide a fallback if the translation is missing
            if ($title === 'admin::app.activities.qualification-updated') {
                $title = "Lead Qualification updated to: {$statusLabel}";
            }

            $activityRepo->create([
                'type' => 'system',
                'title' => $title,
                'is_done' => 1,
                'lead_id' => $model->lead_id,
                'user_id' => $model->user_id,
                'comment' => $model->reason,
            ]);
        });
    }
}
