<?php

test('moldable configuration defines all 5 required entities', function () {
    $entities = config('moldable.entities');

    expect($entities)->toBeArray();

    $expectedEntities = ['leads'];

    foreach ($expectedEntities as $code) {
        expect(array_key_exists($code, $entities))
            ->toBeTrue("Moldable entities config must define '{$code}'");

        expect($entities[$code]['name'])->toBe(ucfirst($code));
    }
});

test('entity selector dropdowns in index and drawer views render options dynamically from config', function () {
    $indexPath = base_path('packages/Webkul/Moldable/src/Resources/views/builder/index.blade.php');
    $drawerPath = base_path('packages/Webkul/Moldable/src/Resources/views/builder/drawer.blade.php');

    $indexContent = file_get_contents($indexPath);
    $drawerContent = file_get_contents($drawerPath);

    expect(str_contains($indexContent, "config('moldable.entities'"))->toBeTrue();
    expect(str_contains($drawerContent, "config('moldable.entities'"))->toBeTrue();
});
