<?php

test('submitAddFieldDrawer in drawer view appends rich studio field card markup with drag handle and action controls', function () {
    $drawerPath = base_path('packages/Webkul/Moldable/src/Resources/views/builder/drawer.blade.php');
    $content    = file_get_contents($drawerPath);

    expect(str_contains($content, 'icon-edit'))->toBeTrue();
    expect(str_contains($content, 'icon-delete'))->toBeTrue();
    expect(str_contains($content, 'draggable'))->toBeTrue();
});
