<?php

namespace Webkul\API\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Webkul\API\Http\Middleware\ApiTokenAuthenticate;
use Webkul\API\Http\Middleware\WebhookAuthenticate;

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
     * @return void
     */
    public function boot(Router $router)
    {
        $router->aliasMiddleware('webkul.api.auth', ApiTokenAuthenticate::class);
        $router->aliasMiddleware('webkul.webhook.auth', WebhookAuthenticate::class);

        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
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
            ->group(__DIR__.'/../Routes/api.php');
    }
}
