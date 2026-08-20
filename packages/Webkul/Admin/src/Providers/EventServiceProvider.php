<?php

namespace Webkul\Admin\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'contacts.person.create.after' => [
            'Webkul\Admin\Listeners\Person@linkToEmail',
        ],

        'lead.create.after' => [
            'Webkul\Admin\Listeners\Lead@linkToEmail',
            'Webkul\Admin\Listeners\Lead@handleStageActions',
        ],

        'lead.update.after' => [
            'Webkul\Admin\Listeners\Lead@handleStageActions',
        ],

    ];

    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [
        \Webkul\Activity\Listeners\LeadTimelineEventSubscriber::class,
    ];
}
