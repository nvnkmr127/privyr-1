<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$repo = app(\Webkul\Lead\Repositories\LeadRepository::class);
\Illuminate\Support\Facades\Event::listen('eloquent.saving: Webkul\Lead\Models\Lead', function ($model) {
    if (array_key_exists('person', $model->getAttributes())) {
        echo "PERSON SET IN SAVING! Trace:\n";
        debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    }
});
$lead = $repo->create([
    'entity_type' => 'leads',
    'title' => 'Test',
    'user_id' => 1,
]);
