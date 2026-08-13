<?php

namespace Webkul\Moldable\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Webkul\Moldable\Http\Middleware\ResolveWorkspace;

class MoldableServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/moldable.php', 'moldable');
    }

    public function boot(Router $router): void
    {
        $router->aliasMiddleware('moldable.workspace', ResolveWorkspace::class);

        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'moldable');
        \Illuminate\Support\Facades\Blade::anonymousComponentPath(__DIR__.'/../Resources/views', 'moldable');

        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
    }
}
