<?php

namespace Webkul\Lead\Listeners;

use Illuminate\Events\Dispatcher;
use Webkul\Lead\Services\Sla\LeadSlaService;
use Webkul\Lead\Models\Lead;
use Webkul\Activity\Models\Activity;

class LeadSlaSubscriber
{
    /**
     * @var LeadSlaService
     */
    protected $leadSlaService;

    public function __construct(LeadSlaService $leadSlaService)
    {
        $this->leadSlaService = $leadSlaService;
    }

    /**
     * Handle lead creation.
     */
    public function onLeadCreated($lead)
    {
        if ($lead instanceof Lead) {
            $this->leadSlaService->handleLeadCreated($lead);
        }
    }

    /**
     * Handle lead assignment.
     */
    public function onLeadAssigned($lead)
    {
        if ($lead instanceof Lead) {
            $this->leadSlaService->handleLeadAssigned($lead);
        }
    }

    /**
     * Handle activity creation.
     */
    public function onActivityCreated($activity)
    {
        if ($activity instanceof Activity && $activity->lead) {
            $this->leadSlaService->handleActivityCreated($activity->lead, $activity);
        }
    }
    
    /**
     * Handle follow-up due.
     */
    public function onFollowUpDue($data)
    {
        if (isset($data['lead']) && $data['lead'] instanceof Lead && isset($data['due_date'])) {
            $this->leadSlaService->handleFollowUpDue($data['lead'], $data['due_date']);
        }
    }
    
    /**
     * Handle follow-up completed.
     */
    public function onFollowUpCompleted($activity)
    {
        if ($activity instanceof Activity && $activity->lead) {
            $this->leadSlaService->handleFollowUpCompleted($activity->lead, $activity);
        }
    }

    /**
     * Handle stage update.
     */
    public function onStageUpdated($lead)
    {
        if ($lead instanceof Lead) {
            $this->leadSlaService->handleStageUpdated($lead);
        }
    }

    /**
     * Handle status change (closing).
     */
    public function onLeadStatusChanged($data)
    {
        if (isset($data['lead']) && $data['lead'] instanceof Lead && isset($data['new_status'])) {
            if (in_array($data['new_status'], ['Converted', 'Lost', 'Junk'])) {
                $this->leadSlaService->resolveAllSlas($data['lead']);
            }
        }
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  Dispatcher  $events
     * @return void
     */
    public function subscribe(Dispatcher $events)
    {
        $events->listen(
            'lead.create.after',
            self::class . '@onLeadCreated'
        );

        $events->listen(
            'lead.assigned',
            self::class . '@onLeadAssigned'
        );

        $events->listen(
            'activity.create.after',
            self::class . '@onActivityCreated'
        );
        
        $events->listen(
            'lead.follow_up.due',
            self::class . '@onFollowUpDue'
        );
        
        $events->listen(
            'lead.follow_up.completed',
            self::class . '@onFollowUpCompleted'
        );

        $events->listen(
            'lead.stage.updated',
            self::class . '@onStageUpdated'
        );

        $events->listen(
            'lead.status.changed',
            self::class . '@onLeadStatusChanged'
        );
    }
}
