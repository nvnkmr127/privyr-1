<?php

use Webkul\Attribute\Models\Attribute;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$attr = Attribute::where('code', 'person')->first();
print_r($attr ? $attr->toArray() : 'null');
