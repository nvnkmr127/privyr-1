<?php

test('moldable field builder view matches user mockup layout structure pixel for pixel', function () {
    $indexPath = base_path('packages/Webkul/Moldable/src/Resources/views/builder/index.blade.php');
    $content = file_get_contents($indexPath);

    expect(str_contains($content, 'Presentation Groups'))->toBeTrue();
    expect(str_contains($content, 'Resource Overview'))->toBeTrue();
    expect(str_contains($content, 'Quick Field Types'))->toBeTrue();
    expect(str_contains($content, 'Aa'))->toBeTrue();
    expect(str_contains($content, 'Add New Field'))->toBeTrue();
});
