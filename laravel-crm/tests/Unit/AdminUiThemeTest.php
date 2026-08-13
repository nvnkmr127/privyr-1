<?php

test('admin UI theme layout app.blade.php exists and features glassmorphism sidebar and workspace switcher', function () {
    $layoutPath = base_path('packages/Webkul/Moldable/src/Resources/views/layouts/app.blade.php');
    expect(file_exists($layoutPath))->toBeTrue();

    $content = file_get_contents($layoutPath);
    expect(str_contains($content, 'id="admin-sidebar"'))->toBeTrue();
    expect(str_contains($content, 'Acme Global Workspace'))->toBeTrue();
    expect(str_contains($content, 'Enterprise CRM Studio'))->toBeTrue();
});

test('field builder view wraps inside x-moldable::layouts.app layout component', function () {
    $indexPath = base_path('packages/Webkul/Moldable/src/Resources/views/builder/index.blade.php');
    $content   = file_get_contents($indexPath);

    expect(str_contains($content, '<x-moldable::layouts.app>'))->toBeTrue();
});
