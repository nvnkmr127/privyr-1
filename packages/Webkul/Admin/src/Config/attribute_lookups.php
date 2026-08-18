<?php

return [
    'leads' => [
        'name' => 'Leads',
        'repository' => 'Webkul\Lead\Repositories\LeadRepository',
        'label_column' => 'title',
    ],

    'lead_sources' => [
        'name' => 'Lead Sources',
        'repository' => 'Webkul\Lead\Repositories\SourceRepository',
    ],

    'lead_types' => [
        'name' => 'Lead Types',
        'repository' => 'Webkul\Lead\Repositories\TypeRepository',
    ],

    'lead_pipelines' => [
        'name' => 'Lead Pipelines',
        'repository' => 'Webkul\Lead\Repositories\PipelineRepository',
    ],

    'lead_pipeline_stages' => [
        'name' => 'Lead Pipeline Stages',
        'repository' => 'Webkul\Lead\Repositories\StageRepository',
    ],

    'users' => [
        'name' => 'Sales Owners',
        'repository' => 'Webkul\User\Repositories\UserRepository',
    ],



    'warehouses' => [
        'name' => 'Warehouses',
        'repository' => 'Webkul\Warehouse\Repositories\WarehouseRepository',
    ],

    'locations' => [
        'name' => 'Locations',
        'repository' => 'Webkul\Warehouse\Repositories\LocationRepository',
    ],
];
