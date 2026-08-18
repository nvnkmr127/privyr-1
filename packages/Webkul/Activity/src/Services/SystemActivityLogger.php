<?php

namespace Webkul\Activity\Services;

use Webkul\Activity\Models\Activity;
use Webkul\Lead\Models\Lead;

class SystemActivityLogger
{
    /**
     * Log a system activity for a Lead.
     */
    public function log(Lead $lead, string $title, array $additionalData = [], ?string $comment = null): Activity
    {
        $activity = new Activity();
        $activity->type = 'system';
        $activity->title = $title;
        $activity->comment = $comment;
        $activity->additional = $additionalData;
        $activity->user_id = auth()->check() ? auth()->id() : 1; // Default to super admin if no user
        $activity->is_done = 1;
        $activity->lead_id = $lead->id;
        $activity->save();

        return $activity;
    }
}
