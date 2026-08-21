<?php

namespace Webkul\Lead\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Event;
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
     * Sync the Lead's next action properties with its earliest pending Activity.
     * This denormalizes the Next Action data for DataGrid performance.
     */
    public function syncNextAction(Lead $lead): Lead
    {
        $oldNextAction = $lead->next_action;

        $nextActivity = $lead->activities()
            ->whereNotIn('type', ['system', 'note'])
            ->where('status', 'pending')
            ->orderBy('schedule_from', 'asc')
            ->first();

        if ($nextActivity) {
            return $this->leadRepository->update([
                'entity_type' => 'leads',
                'next_action' => $nextActivity->type,
                'next_follow_up_at' => $nextActivity->schedule_from,
                'follow_up_owner_id' => $nextActivity->user_id,
                'next_action_priority' => $nextActivity->priority,
                'next_action_note' => $nextActivity->title ?? $nextActivity->comment,
            ], $lead->id);
        }

        $this->leadRepository->update([
            'entity_type' => 'leads',
            'next_action' => null,
            'next_follow_up_at' => null,
            'follow_up_owner_id' => null,
            'next_action_priority' => null,
            'next_action_note' => null,
        ], $lead->id);

        if ($oldNextAction) {
            Event::dispatch('lead.no_next_action', $lead);
        }

        return $lead;
    }

    /**
     * Schedule a follow-up for a lead.
     */
    public function schedule(Lead $lead, string $nextAction, string $date, ?int $ownerId = null): Lead
    {
        $activity = Activity::create([
            'title' => 'Scheduled Next Action: '.$nextAction,
            'type' => $nextAction,
            'status' => 'pending',
            'is_done' => 0,
            'lead_id' => $lead->id,
            'user_id' => $ownerId ?? auth()->id() ?? $lead->user_id,
            'schedule_from' => Carbon::parse($date),
            'schedule_to' => Carbon::parse($date)->addMinutes(30),
        ]);

        $this->syncNextAction($lead);

        Event::dispatch('lead.follow_up.created', $lead);

        return $lead;
    }

    /**
     * Snooze an existing follow-up.
     */
    public function snooze(Lead $lead, string $date): Lead
    {
        $nextActivity = $lead->activities()
            ->where('status', 'pending')
            ->orderBy('schedule_from', 'asc')
            ->first();

        if ($nextActivity) {
            $originalDate = $nextActivity->schedule_from;
            $newDate = Carbon::parse($date);

            // Record reschedule history
            $additional = $nextActivity->additional ?? [];
            $additional['reschedule_history'][] = [
                'original_date' => $originalDate ? $originalDate->toDateTimeString() : null,
                'new_date' => $newDate->toDateTimeString(),
                'user_id' => auth()->id(),
                'timestamp' => Carbon::now()->toDateTimeString(),
            ];

            $nextActivity->update([
                'schedule_from' => $newDate,
                'schedule_to' => $newDate->addMinutes(30),
                'additional' => $additional,
            ]);

            $this->syncNextAction($lead);

            Event::dispatch('lead.follow_up.rescheduled', $lead);
        }

        return $lead;
    }

    /**
     * Complete the current follow-up.
     */
    public function complete(Lead $lead, string $note = ''): Lead
    {
        $nextActivity = $lead->activities()
            ->whereNotIn('type', ['system', 'note'])
            ->where('status', 'pending')
            ->orderBy('schedule_from', 'asc')
            ->first();



        if ($nextActivity) {
            $updated = $nextActivity->update([
                'status' => 'completed',
                'is_done' => 1,
                'completed_at' => Carbon::now(),
                'completed_by_id' => auth()->id() ?? $lead->user_id,
                'comment' => $note ? ($nextActivity->comment ? $nextActivity->comment."\n".$note : $note) : $nextActivity->comment,
            ]);



            $this->markContacted($lead);
            $this->syncNextAction($lead);

            Event::dispatch('lead.follow_up.completed', $lead);
        }

        return $lead;
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

    /**
     * Cancel open follow-ups.
     */
    public function cancelOpenFollowUps(Lead $lead): Lead
    {
        $pendingActivities = $lead->activities()
            ->whereNotIn('type', ['system', 'note'])
            ->where('status', 'pending')
            ->get();

        foreach ($pendingActivities as $activity) {
            $activity->update([
                'status' => 'completed', // Treating as completed/cancelled
                'is_done' => 1,
                'completed_at' => Carbon::now(),
                'completed_by_id' => auth()->id() ?? $lead->user_id,
                'comment' => $activity->comment ? $activity->comment."\nCancelled due to lead closure" : 'Cancelled due to lead closure',
            ]);
        }

        $this->syncNextAction($lead);

        return $lead;
    }
}
