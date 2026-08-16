<?php

namespace App\Providers;

use App\Listeners\SendLeadAgentPushNotification;
use App\Listeners\SendLeadWhatsAppNotification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Memoize the resolved tenant for the whole request.
        $this->app->singleton(\App\Support\WorkspaceContext::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen('lead.create.after', SendLeadWhatsAppNotification::class);
        Event::listen('lead.create.after', SendLeadAgentPushNotification::class);
    }
}
