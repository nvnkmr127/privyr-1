<?php

test('app.blade.php layout imports and applies clean sans-serif font family stack Inter, Roboto, Lato, Open Sans', function () {
    $layoutPath = base_path('packages/Webkul/Moldable/src/Resources/views/layouts/app.blade.php');
    $content = file_get_contents($layoutPath);

    expect(str_contains($content, 'Roboto'))->toBeTrue();
    expect(str_contains($content, 'Inter'))->toBeTrue();
    expect(str_contains($content, 'Lato'))->toBeTrue();
    expect(str_contains($content, 'Open Sans'))->toBeTrue();
});
