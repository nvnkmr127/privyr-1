<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$metadata = [
    'routes' => [],
    'tables' => [],
    'packages' => [],
];

// Extract Routes
$routes = Route::getRoutes();
foreach ($routes as $route) {
    $metadata['routes'][] = [
        'uri' => $route->uri(),
        'methods' => $route->methods(),
        'action' => $route->getActionName(),
        'name' => $route->getName(),
        'middleware' => $route->middleware(),
    ];
}

// Extract Database Schema
$schema = Schema::getConnection()->getSchemaBuilder();
$tables = $schema->getTables();
foreach ($tables as $tableInfo) {
    $table = $tableInfo['name'];
    $columns = $schema->getColumns($table);

    $foreignKeys = [];
    try {
        $foreignKeys = $schema->getForeignKeys($table);
    } catch (Exception $e) {
    }

    $metadata['tables'][$table] = [
        'columns' => $columns,
        'foreign_keys' => $foreignKeys,
    ];
}

// Packages
$packagePath = base_path('packages/Webkul');
if (is_dir($packagePath)) {
    $metadata['packages'] = array_values(array_diff(scandir($packagePath), ['..', '.']));
}

file_put_contents(base_path('docs/metadata/system_metadata.json'), json_encode($metadata, JSON_PRETTY_PRINT));
echo "Metadata extracted successfully to docs/metadata/system_metadata.json\n";
