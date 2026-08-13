<?php

test('add field drawer contains all 6 required sections', function () {
    $viewPath = base_path('packages/Webkul/Moldable/src/Resources/views/builder/drawer.blade.php');
    expect(file_exists($viewPath))->toBeTrue();

    $content = file_get_contents($viewPath);

    $requiredSections = [
        '1. Basic Information',
        '2. Field Type',
        '3. Configuration',
        '4. Validation',
        '5. Visibility',
        '6. Advanced',
    ];

    foreach ($requiredSections as $section) {
        expect(str_contains($content, $section))
            ->toBeTrue("Add Field Drawer should contain section '{$section}'");
    }
});
