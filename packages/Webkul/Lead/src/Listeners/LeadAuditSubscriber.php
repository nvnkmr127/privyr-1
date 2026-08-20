<?php

namespace Webkul\Lead\Listeners;

use Webkul\Lead\Models\Lead;
use Webkul\Lead\Models\LeadAudit;

class LeadAuditSubscriber
{
    /**
     * Trackable fields and their human readable names.
     */
    protected $trackableFields = [
        'lead_pipeline_stage_id' => 'Stage',
        'status' => 'Status',
        'user_id' => 'Owner',
        'expected_close_date' => 'Expected Close Date',
        'rotten_days' => 'Rotten Days',
        'is_qualified' => 'Qualified',
    ];

    /**
     * Handle lead created events.
     */
    public function onLeadCreated($event)
    {
        $this->recordAudit($event, clone $event, 'created');
    }

    /**
     * Handle lead updated events.
     */
    public function onLeadUpdated($event)
    {
        // Eloquent 'updated' event passes the model
        $lead = $event;

        foreach ($lead->getDirty() as $field => $newValue) {
            if (array_key_exists($field, $this->trackableFields)) {
                $oldValue = $lead->getOriginal($field);
                
                // Don't audit if values haven't actually changed
                if ($oldValue == $newValue) {
                    continue;
                }

                $this->recordAudit($lead, $lead, 'updated', $field, $oldValue, $newValue);
            }
        }
    }

    /**
     * Handle lead deleted events.
     */
    public function onLeadDeleted($event)
    {
        $this->recordAudit($event, $event, 'deleted');
    }

    /**
     * Record an audit entry.
     */
    protected function recordAudit($lead, $context, $action, $field = null, $oldValue = null, $newValue = null)
    {
        // Default source is Manual, unless overridden in request or via context
        $source = request()->has('audit_source') ? request()->get('audit_source') : 'Manual';

        LeadAudit::create([
            'lead_id'    => $lead->id,
            'user_id'    => auth()->check() ? auth()->id() : null,
            'action'     => $action,
            'field'      => $field,
            'old_value'  => $oldValue,
            'new_value'  => $newValue,
            'source'     => $source,
            'request_id' => request()->header('X-Request-Id'),
        ]);
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'eloquent.created: Webkul\Lead\Models\Lead',
            [LeadAuditSubscriber::class, 'onLeadCreated']
        );

        $events->listen(
            'eloquent.updated: Webkul\Lead\Models\Lead',
            [LeadAuditSubscriber::class, 'onLeadUpdated']
        );

        $events->listen(
            'eloquent.deleted: Webkul\Lead\Models\Lead',
            [LeadAuditSubscriber::class, 'onLeadDeleted']
        );
    }
}
