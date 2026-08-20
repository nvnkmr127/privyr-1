<?php

namespace Webkul\Lead\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Webkul\Lead\Console\Commands\EvaluateLeadHealth;
use Webkul\Lead\Contracts\LeadIngestionService;
use Webkul\Lead\Listeners\LeadAuditSubscriber;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Observers\LeadObserver;
use Webkul\Lead\Policies\LeadPolicy;

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

        Gate::policy(\Webkul\Lead\Contracts\Lead::class, LeadPolicy::class);
        Gate::policy(Lead::class, LeadPolicy::class);

        Lead::observe(LeadObserver::class);

        Event::subscribe(LeadAuditSubscriber::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                EvaluateLeadHealth::class,
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
            LeadIngestionService::class,
            \Webkul\Lead\Services\LeadIngestionService::class
        );
    }
}
