<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Event::listen('lead.create.after', \App\Listeners\SendLeadWhatsAppNotification::class);
        \Illuminate\Support\Facades\Event::listen('lead.create.after', \App\Listeners\SendLeadAgentPushNotification::class);
    }
}
