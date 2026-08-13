<?php

return [
    'entities' => [
        'leads' => [
            'code'  => 'leads',
            'name'  => 'Leads',
            'icon'  => 'icon-lead',
            'model' => \Webkul\Lead\Models\Lead::class,
        ],
        'persons' => [
            'code'  => 'persons',
            'name'  => 'Persons',
            'icon'  => 'icon-person',
            'model' => \Webkul\Contact\Models\Person::class,
        ],
        'organizations' => [
            'code'  => 'organizations',
            'name'  => 'Organizations',
            'icon'  => 'icon-organization',
            'model' => \Webkul\Contact\Models\Organization::class,
        ],
        'products' => [
            'code'  => 'products',
            'name'  => 'Products',
            'icon'  => 'icon-product',
            'model' => \Webkul\Product\Models\Product::class,
        ],
        'quotes' => [
            'code'  => 'quotes',
            'name'  => 'Quotes',
            'icon'  => 'icon-quote',
            'model' => \Webkul\Quote\Models\Quote::class,
        ],
    ],
];
