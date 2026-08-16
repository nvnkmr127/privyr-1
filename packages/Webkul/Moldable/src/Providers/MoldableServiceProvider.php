<?php

namespace Webkul\Moldable\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Webkul\Attribute\Models\Attribute;
use Webkul\Moldable\Http\Middleware\ResolveWorkspace;
use Webkul\Moldable\Models\FieldGroupAttribute;

class MoldableServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/moldable.php', 'moldable');
    }

    public function boot(Router $router): void
    {
        $router->aliasMiddleware('moldable.workspace', ResolveWorkspace::class);

        // The field-group pivot cannot FK to attributes.id (core uses INT, the
        // pivot uses BIGINT), so clean up its rows whenever an attribute is
        // deleted — through the builder or the core admin — to avoid orphans.
        Attribute::deleted(function (Attribute $attribute) {
            FieldGroupAttribute::where('attribute_id', $attribute->id)->delete();
        });

        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'moldable');
        Blade::anonymousComponentPath(__DIR__.'/../Resources/views', 'moldable');

        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
    }
}
