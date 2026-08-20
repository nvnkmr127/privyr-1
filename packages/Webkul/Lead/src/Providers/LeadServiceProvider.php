<?php

namespace Webkul\Lead\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Observers\LeadObserver;

class LeadServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(Router $router)
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        \Illuminate\Support\Facades\Gate::policy(\Webkul\Lead\Contracts\Lead::class, \Webkul\Lead\Policies\LeadPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\Webkul\Lead\Models\Lead::class, \Webkul\Lead\Policies\LeadPolicy::class);

        Lead::observe(LeadObserver::class);
        
        \Illuminate\Support\Facades\Event::subscribe(\Webkul\Lead\Listeners\LeadAuditSubscriber::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                \Webkul\Lead\Console\Commands\EvaluateLeadHealth::class,
            ]);
        }
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(
            \Webkul\Lead\Contracts\LeadIngestionService::class,
            \Webkul\Lead\Services\LeadIngestionService::class
        );
    }
}
