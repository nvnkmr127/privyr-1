<?php

test('field builder view is a standalone clean Laravel page without Webkul or Krayin admin layout wrappers', function () {
    $indexPath = base_path('packages/Webkul/Moldable/src/Resources/views/builder/index.blade.php');
    $layoutPath = base_path('packages/Webkul/Moldable/src/Resources/views/layouts/app.blade.php');
    $indexContent = file_get_contents($indexPath);
    $layoutContent = file_exists($layoutPath) ? file_get_contents($layoutPath) : $indexContent;

    expect(str_contains($layoutContent, '<!DOCTYPE html>'))->toBeTrue();
    expect(str_contains($indexContent, '<x-admin::layouts>'))->toBeFalse();
    expect(str_contains($layoutContent, 'tailwindcss.com'))->toBeTrue();
    expect(str_contains($layoutContent, 'Moldable CRM') || str_contains($indexContent, 'Moldable CRM'))->toBeTrue();
});
