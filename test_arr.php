<?php

use Illuminate\Support\Arr;

require __DIR__.'/vendor/autoload.php';
$payload = [
    'full_name' => 'Alice Walker',
    'email' => 'alice@globex.org',
    'phone' => '+15551234',
    'incoming_priority' => 'High Priority P1',
];
$mappedData = [
    'title' => null,
    'description' => null,
    'lead_value' => null,
    'person' => [
        'name' => 'Alice Walker',
        'emails' => 'alice@globex.org',
        'contact_numbers' => '+15551234',
    ],
    'deal_prio_xyz' => 'High Priority P1',
];
$customLeadAttributes = Arr::except($mappedData, ['person', 'title', 'description', 'lead_value']);
print_r($customLeadAttributes);
