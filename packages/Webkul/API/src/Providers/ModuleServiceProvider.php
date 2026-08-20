<?php

namespace Webkul\API\Providers;

use Webkul\Core\Providers\CoreModuleServiceProvider;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    protected $models = [
        \Webkul\API\Models\ApiCredential::class,
    ];
}
