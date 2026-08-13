<?php

test('moldable field builder screen features a 2-column studio layout with group navigation and summary stats', function () {
    $indexPath = base_path('packages/Webkul/Moldable/src/Resources/views/builder/index.blade.php');
    $content   = file_get_contents($indexPath);

    expect(str_contains($content, 'lg:grid-cols-12'))->toBeTrue();
    expect(str_contains($content, 'id="field-groups-nav"'))->toBeTrue();
    expect(str_contains($content, 'id="stat-total-fields"'))->toBeTrue();
    expect(str_contains($content, 'openNewGroupModal()'))->toBeTrue();
    expect(str_contains($content, 'handleDragStart(event)'))->toBeTrue();
});
