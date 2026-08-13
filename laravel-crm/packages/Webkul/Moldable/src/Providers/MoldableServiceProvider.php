<?php

namespace Webkul\Moldable\Providers;

use Illuminate\Support\ServiceProvider;

class MoldableServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');
    }
}
