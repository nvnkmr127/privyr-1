<?php

namespace Webkul\Lead\Services;

use Carbon\Carbon;
use Webkul\Activity\Models\Activity;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Repositories\LeadRepository;

class LeadFollowUpService
{
    protected $leadRepository;

    public function __construct(LeadRepository $leadRepository)
    {
        $this->leadRepository = $leadRepository;
    }

    /**
     * Schedule a follow-up for a lead.
     */
    public function schedule(Lead $lead, string $nextAction, string $date, ?int $ownerId = null): Lead
    {
        return $this->leadRepository->update([
            'entity_type' => 'leads',
            'next_action' => $nextAction,
            'next_follow_up_at' => Carbon::parse($date),
            'follow_up_owner_id' => $ownerId ?? auth()->id(),
        ], $lead->id);
    }

    /**
     * Snooze an existing follow-up.
     */
    public function snooze(Lead $lead, string $date): Lead
    {
        return $this->leadRepository->update([
            'entity_type' => 'leads',
            'next_follow_up_at' => Carbon::parse($date),
        ], $lead->id);
    }

    /**
     * Complete the current follow-up.
     */
    public function complete(Lead $lead, string $note = ''): Lead
    {
        if (! $lead->next_action) {
            return $lead;
        }

        // Log the completed action as an activity on the timeline
        Activity::create([
            'title' => 'Completed Follow-up: '.$lead->next_action,
            'type' => 'system',
            'comment' => $note,
            'is_done' => 1,
            'lead_id' => $lead->id,
            'user_id' => auth()->id() ?? $lead->user_id,
            'schedule_from' => Carbon::now(),
            'schedule_to' => Carbon::now(),
        ]);

        return $this->leadRepository->update([
            'entity_type' => 'leads',
            'next_action' => null,
            'next_follow_up_at' => null,
            'follow_up_owner_id' => null,
            'last_contacted_at' => Carbon::now(),
            'is_unread' => false,
        ], $lead->id);
    }

    /**
     * Mark a lead as contacted manually.
     */
    public function markContacted(Lead $lead): Lead
    {
        return $this->leadRepository->update([
            'entity_type' => 'leads',
            'last_contacted_at' => Carbon::now(),
            'is_unread' => false,
        ], $lead->id);
    }
}
