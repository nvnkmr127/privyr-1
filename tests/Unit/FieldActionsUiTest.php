<?php

test('field builder view contains edit and delete action controls with icon-edit and icon-delete', function () {
    $indexPath = base_path('packages/Webkul/Moldable/src/Resources/views/builder/index.blade.php');
    $content = file_get_contents($indexPath);

    expect(str_contains($content, 'icon-edit'))->toBeTrue();
    expect(str_contains($content, 'icon-delete'))->toBeTrue();
    expect(str_contains($content, 'openAddFieldDrawer()'))->toBeTrue();
});
