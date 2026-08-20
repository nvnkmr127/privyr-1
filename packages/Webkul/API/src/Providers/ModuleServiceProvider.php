<?php

namespace Webkul\API\Providers;

use Webkul\API\Models\ApiCredential;
use Webkul\Core\Providers\CoreModuleServiceProvider;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    protected $models = [
        ApiCredential::class,
    ];
}
