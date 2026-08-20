<?php

namespace Webkul\API\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class ApiServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function register()
    {
        // Register configuration or other bindings
    }

    /**
     * Bootstrap any application services.
     *
     * @param \Illuminate\Routing\Router $router
     * @return void
     */
    public function boot(\Illuminate\Routing\Router $router)
    {
        $router->aliasMiddleware('webkul.api.auth', \Webkul\API\Http\Middleware\ApiTokenAuthenticate::class);
        $router->aliasMiddleware('webkul.webhook.auth', \Webkul\API\Http\Middleware\WebhookAuthenticate::class);

        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->registerRoutes();
    }

    /**
     * Register the API routes.
     *
     * @return void
     */
    protected function registerRoutes()
    {
        Route::prefix('api/v1')
            ->middleware('api')
            ->namespace('Webkul\API\Http\Controllers')
            ->group(__DIR__ . '/../Routes/api.php');
    }
}
