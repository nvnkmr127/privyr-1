<?php

namespace Webkul\Activity\Listeners;

use Webkul\Activity\Services\SystemActivityLogger;
use Webkul\Lead\Models\Lead;
use Webkul\User\Models\User;
use Webkul\Attribute\Models\AttributeOption;

class LeadTimelineEventSubscriber
{
    /**
     * Create the event subscriber.
     *
     * @return void
     */
    public function __construct(
        protected SystemActivityLogger $systemActivityLogger
    ) {}

    /**
     * Handle lead assigned events.
     */
    public function handleLeadAssigned($event)
    {
        $lead = $event;
        $userName = $lead->user ? $lead->user->name : 'Unassigned';
        
        $this->systemActivityLogger->log(
            $lead,
            'Lead assigned',
            ['old' => ['label' => 'Unknown'], 'new' => ['label' => $userName]],
            "Lead was assigned to {$userName}"
        );
    }

    /**
     * Handle lead status changed events.
     */
    public function handleLeadStatusChanged($lead)
    {
        $statusName = $lead->status;
        
        $this->systemActivityLogger->log(
            $lead,
            'Status changed',
            ['old' => ['label' => 'Previous Status'], 'new' => ['label' => $statusName]],
            "Lead status changed to {$statusName}"
        );
    }

    /**
     * Handle lead stage changed events.
     */
    public function handleLeadStageChanged($lead)
    {
        $stageName = $lead->stage ? $lead->stage->name : 'Unknown';
        
        $this->systemActivityLogger->log(
            $lead,
            'Stage changed',
            ['old' => ['label' => 'Previous Stage'], 'new' => ['label' => $stageName]],
            "Lead stage changed to {$stageName}"
        );
    }

    /**
     * Handle lead qualified events.
     */
    public function handleLeadQualified($lead)
    {
        $this->systemActivityLogger->log(
            $lead,
            'Lead qualified',
            ['old' => ['label' => 'Unqualified'], 'new' => ['label' => 'Qualified']],
            "Lead was marked as Qualified"
        );
    }

    /**
     * Handle lead nurturing started events.
     */
    public function handleLeadNurturingStarted($lead)
    {
        $reason = $lead->nurture_reason_id ? AttributeOption::find($lead->nurture_reason_id)?->name : 'Unspecified';
        $this->systemActivityLogger->log(
            $lead,
            'Nurturing started',
            ['old' => ['label' => 'Not Nurturing'], 'new' => ['label' => 'Nurturing']],
            "Lead entered Nurturing. Reason: {$reason}"
        );
    }

    /**
     * Handle lead nurturing completed events.
     */
    public function handleLeadNurturingCompleted($lead)
    {
        $this->systemActivityLogger->log(
            $lead,
            'Nurturing completed',
            ['old' => ['label' => 'Nurturing'], 'new' => ['label' => 'Completed']],
            "Lead exited Nurturing."
        );
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     * @return void
     */
    public function subscribe($events)
    {
        $events->listen(
            'lead.assigned',
            [LeadTimelineEventSubscriber::class, 'handleLeadAssigned']
        );

        $events->listen(
            'lead.status.changed',
            [LeadTimelineEventSubscriber::class, 'handleLeadStatusChanged']
        );

        $events->listen(
            'lead.stage.changed',
            [LeadTimelineEventSubscriber::class, 'handleLeadStageChanged']
        );

        $events->listen(
            'lead.qualification.qualified',
            [LeadTimelineEventSubscriber::class, 'handleLeadQualified']
        );

        $events->listen(
            'lead.nurturing.started',
            [LeadTimelineEventSubscriber::class, 'handleLeadNurturingStarted']
        );

        $events->listen(
            'lead.nurturing.completed',
            [LeadTimelineEventSubscriber::class, 'handleLeadNurturingCompleted']
        );
    }
}
