<?php

namespace Webkul\Activity\Observers;

use Carbon\Carbon;
use Webkul\Activity\Models\Activity;
use Webkul\Lead\Models\Lead;

class ActivityObserver
{
    /**
     * Handle the Activity "created" event.
     */
    public function created(Activity $activity): void
    {
        $this->updateLeadTimestamps($activity);
    }

    /**
     * Handle the Activity "updated" event.
     */
    public function updated(Activity $activity): void
    {
        $this->updateLeadTimestamps($activity);
    }

    /**
     * Update the related Lead timestamps based on activity type and status.
     */
    protected function updateLeadTimestamps(Activity $activity): void
    {
        if (! $activity->lead_id) {
            return;
        }

        // We use finding the lead to avoid triggering other observers unnecessarily if possible,
        // but we want to save it so timestamps update.
        $lead = Lead::find($activity->lead_id);

        if (! $lead) {
            return;
        }

        $leadModified = false;

        // Any meaningful activity counts for `last_activity_at`
        if ($activity->type !== 'system') {
            $lead->last_activity_at = Carbon::now();
            $leadModified = true;
        }

        // Only meaningful contact counts for `last_contacted_at`
        if ($activity->status === 'completed' && in_array($activity->type, ['call', 'meeting', 'email', 'whatsapp'])) {
            $lead->last_contacted_at = $activity->completed_at ?? Carbon::now();
            $leadModified = true;
        }

        if ($leadModified) {
            // Use saveQuietly to prevent LeadObserver from logging this as a generic update,
            // since the Activity itself is already the log of the action.
            $lead->saveQuietly();
        }
    }
}
