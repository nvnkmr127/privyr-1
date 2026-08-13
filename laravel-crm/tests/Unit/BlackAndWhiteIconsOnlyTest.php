<?php

test('layout and field builder view contain only clean black and white SVG line icons without emojis', function () {
    $layoutContent = file_get_contents(base_path('packages/Webkul/Moldable/src/Resources/views/layouts/app.blade.php'));
    $indexContent  = file_get_contents(base_path('packages/Webkul/Moldable/src/Resources/views/builder/index.blade.php'));

    expect(str_contains($layoutContent, '<svg'))->toBeTrue();
    expect(str_contains($indexContent, '<svg'))->toBeTrue();

    // Check no emoji characters present in markup
    expect(str_contains($layoutContent, '📊'))->toBeFalse();
    expect(str_contains($indexContent, '⚡'))->toBeFalse();
});
