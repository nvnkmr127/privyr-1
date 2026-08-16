<?php

test('redesigned view page features entity switcher pills, quick field types palette, and resource metrics', function () {
    $indexPath = base_path('packages/Webkul/Moldable/src/Resources/views/builder/index.blade.php');
    $content   = file_get_contents($indexPath);

    expect(str_contains($content, 'switchEntityPill('))->toBeTrue();
    expect(str_contains($content, 'Quick Field Types'))->toBeTrue();
    expect(str_contains($content, 'Resource Metrics'))->toBeTrue();
    expect(str_contains($content, 'Moldable CRM v2.0'))->toBeTrue();
});
