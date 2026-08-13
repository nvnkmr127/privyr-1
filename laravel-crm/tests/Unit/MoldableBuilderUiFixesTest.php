<?php

test('field builder view matches Krayin native sticky layout and contains selectGroupTab handler', function () {
    $indexPath = base_path('packages/Webkul/Moldable/src/Resources/views/builder/index.blade.php');
    $content   = file_get_contents($indexPath);

    expect(str_contains($content, 'scroll-reactive-sticky'))->toBeTrue();
    expect(str_contains($content, 'selectGroupTab('))->toBeTrue();
    expect(str_contains($content, 'Add Field'))->toBeTrue();
    expect(str_contains($content, '+ Add Field'))->toBeFalse(); // No duplicate plus symbol
});
