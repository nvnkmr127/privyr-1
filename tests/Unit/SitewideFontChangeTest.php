<?php

test('sitewide layouts apply clean sans-serif font stack Inter, Roboto, Lato, Open Sans', function () {
    $adminIndex = file_get_contents(base_path('packages/Webkul/Admin/src/Resources/views/components/layouts/index.blade.php'));
    $adminAnonymous = file_get_contents(base_path('packages/Webkul/Admin/src/Resources/views/components/layouts/anonymous.blade.php'));
    $moldableApp = file_get_contents(base_path('packages/Webkul/Moldable/src/Resources/views/layouts/app.blade.php'));

    foreach ([$adminIndex, $adminAnonymous, $moldableApp] as $content) {
        expect(str_contains($content, 'Roboto'))->toBeTrue();
        expect(str_contains($content, 'Lato'))->toBeTrue();
        expect(str_contains($content, 'Open Sans'))->toBeTrue();
    }
});
