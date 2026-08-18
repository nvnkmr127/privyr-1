<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$repo = app(\Webkul\Lead\Repositories\LeadRepository::class);
$lead = $repo->create([
    'entity_type' => 'leads',
    'title' => 'Test',
    'user_id' => 1,
]);
print_r(array_keys($lead->getAttributes()));
