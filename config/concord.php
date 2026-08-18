<?php

use Webkul\Admin\Providers\ModuleServiceProvider as AdminModuleServiceProvider;
use Webkul\Attribute\Providers\ModuleServiceProvider as AttributeModuleServiceProvider;
use Webkul\Automation\Providers\ModuleServiceProvider as AutomationModuleServiceProvider;
use Webkul\Core\Providers\ModuleServiceProvider as CoreModuleServiceProvider;
use Webkul\DataGrid\Providers\ModuleServiceProvider as DataGridModuleServiceProvider;
use Webkul\DataTransfer\Providers\ModuleServiceProvider as DataTransferModuleServiceProvider;
use Webkul\Email\Providers\ModuleServiceProvider as EmailModuleServiceProvider;
use Webkul\EmailTemplate\Providers\ModuleServiceProvider as EmailTemplateModuleServiceProvider;
use Webkul\Lead\Providers\ModuleServiceProvider as LeadModuleServiceProvider;
use Webkul\Tag\Providers\ModuleServiceProvider as TagModuleServiceProvider;
use Webkul\User\Providers\ModuleServiceProvider as UserModuleServiceProvider;
use Webkul\WebForm\Providers\ModuleServiceProvider as WebFormModuleServiceProvider;

return [
    'modules' => [
        AdminModuleServiceProvider::class,
        AttributeModuleServiceProvider::class,
        AutomationModuleServiceProvider::class,
        CoreModuleServiceProvider::class,
        DataTransferModuleServiceProvider::class,
        DataGridModuleServiceProvider::class,
        EmailTemplateModuleServiceProvider::class,
        EmailModuleServiceProvider::class,
        LeadModuleServiceProvider::class,
        TagModuleServiceProvider::class,
        UserModuleServiceProvider::class,
        WebFormModuleServiceProvider::class,
    ],

    'register_route_models' => true,
];
