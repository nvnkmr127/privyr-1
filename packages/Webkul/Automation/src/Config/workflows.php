<?php

return [
    'trigger_entities' => [

        'leads' => [
            'name' => 'Leads',
            'class' => 'Webkul\Automation\Helpers\Entity\Lead',
            'events' => [
                [
                    'event' => 'lead.create.after',
                    'name' => 'Created',
                ], [
                    'event' => 'lead.update.after',
                    'name' => 'Updated',
                ], [
                    'event' => 'lead.delete.before',
                    'name' => 'Deleted',
                ], [
                    'event' => 'lead.status.changed',
                    'name' => 'Status Changed',
                ], [
                    'event' => 'lead.stage.changed',
                    'name' => 'Stage Changed',
                ], [
                    'event' => 'lead.owner.changed',
                    'name' => 'Owner Changed',
                ], [
                    'event' => 'lead.team.changed',
                    'name' => 'Team Changed',
                ], [
                    'event' => 'lead.qualification.changed',
                    'name' => 'Qualification Changed',
                ], [
                    'event' => 'lead.priority.changed',
                    'name' => 'Priority Changed',
                ], [
                    'event' => 'lead.temperature.changed',
                    'name' => 'Temperature Changed',
                ], [
                    'event' => 'lead.tag.added',
                    'name' => 'Tag Added',
                ], [
                    'event' => 'lead.tag.removed',
                    'name' => 'Tag Removed',
                ], [
                    'event' => 'lead.follow_up.created',
                    'name' => 'Follow-up Created',
                ], [
                    'event' => 'lead.follow_up.completed',
                    'name' => 'Follow-up Completed',
                ], [
                    'event' => 'lead.follow_up.overdue',
                    'name' => 'Follow-up Overdue',
                ], [
                    'event' => 'lead.follow_up.rescheduled',
                    'name' => 'Follow-up Rescheduled',
                ], [
                    'event' => 'lead.no_next_action',
                    'name' => 'No Next Action',
                ], [
                    'event' => 'lead.inactive',
                    'name' => 'Lead Inactive',
                ], [
                    'event' => 'lead.stage.aging',
                    'name' => 'Stage Aging',
                ], [
                    'event' => 'lead.nurturing.entered',
                    'name' => 'Lead Entered Nurturing',
                ], [
                    'event' => 'lead.nurturing.due',
                    'name' => 'Nurture Due',
                ], [
                    'event' => 'lead.nurturing.reengaged',
                    'name' => 'Nurture Re-engaged',
                ], [
                    'event' => 'lead.communication.received',
                    'name' => 'Communication Received',
                ], [
                    'event' => 'lead.communication.sent',
                    'name' => 'Communication Sent',
                ], [
                    'event' => 'lead.communication.failed',
                    'name' => 'Communication Failed',
                ], [
                    'event' => 'lead.source.received',
                    'name' => 'Source Received',
                ], [
                    'event' => 'lead.assigned',
                    'name' => 'Lead Assigned',
                ], [
                    'event' => 'lead.unassigned',
                    'name' => 'Lead Unassigned',
                ], [
                    'event' => 'lead.sla.started',
                    'name' => 'SLA Started',
                ], [
                    'event' => 'lead.sla.due_soon',
                    'name' => 'SLA Due Soon',
                ], [
                    'event' => 'lead.sla.breached',
                    'name' => 'SLA Breached',
                ], [
                    'event' => 'lead.sla.resolved',
                    'name' => 'SLA Resolved',
                ],
            ],
        ],

        'activities' => [
            'name' => 'Activities',
            'class' => 'Webkul\Automation\Helpers\Entity\Activity',
            'events' => [
                [
                    'event' => 'activity.create.after',
                    'name' => 'Created',
                ], [
                    'event' => 'activity.update.after',
                    'name' => 'Updated',
                ], [
                    'event' => 'activity.delete.before',
                    'name' => 'Deleted',
                ],
            ],
        ],

        'quotes' => [
            'name' => 'Quotes',
            'class' => 'Webkul\Automation\Helpers\Entity\Quote',
            'events' => [
                [
                    'event' => 'quote.create.after',
                    'name' => 'Created',
                ], [
                    'event' => 'quote.update.after',
                    'name' => 'Updated',
                ], [
                    'event' => 'quote.delete.before',
                    'name' => 'Deleted',
                ],
            ],
        ],
    ],
];
