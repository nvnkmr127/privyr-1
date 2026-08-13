<?php

test('field builder view is a standalone clean Laravel page without Webkul or Krayin admin layout wrappers', function () {
    $indexPath = base_path('packages/Webkul/Moldable/src/Resources/views/builder/index.blade.php');
    $content   = file_get_contents($indexPath);

    expect(str_contains($content, '<!DOCTYPE html>'))->toBeTrue();
    expect(str_contains($content, '<x-admin::layouts>'))->toBeFalse();
    expect(str_contains($content, 'tailwindcss.com'))->toBeTrue();
    expect(str_contains($content, 'Moldable CRM')).toBeTrue();
});
