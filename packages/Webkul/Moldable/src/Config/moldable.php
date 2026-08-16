<?php

use Webkul\Contact\Models\Organization;
use Webkul\Contact\Models\Person;
use Webkul\Lead\Models\Lead;
use Webkul\Product\Models\Product;
use Webkul\Quote\Models\Quote;

return [
    'entities' => [
        'leads' => [
            'code' => 'leads',
            'name' => 'Leads',
            'icon' => 'icon-lead',
            'model' => Lead::class,
        ],
        'persons' => [
            'code' => 'persons',
            'name' => 'Persons',
            'icon' => 'icon-person',
            'model' => Person::class,
        ],
        'organizations' => [
            'code' => 'organizations',
            'name' => 'Organizations',
            'icon' => 'icon-organization',
            'model' => Organization::class,
        ],
        'products' => [
            'code' => 'products',
            'name' => 'Products',
            'icon' => 'icon-product',
            'model' => Product::class,
        ],
        'quotes' => [
            'code' => 'quotes',
            'name' => 'Quotes',
            'icon' => 'icon-quote',
            'model' => Quote::class,
        ],
    ],
];
