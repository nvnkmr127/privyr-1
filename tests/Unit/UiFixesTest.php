<?php

test('UI fixes: drawer overlay z-index is z-[10000] and backdrop click handler is active', function () {
    $drawerPath = base_path('packages/Webkul/Moldable/src/Resources/views/builder/drawer.blade.php');
    $content = file_get_contents($drawerPath);

    expect(str_contains($content, 'z-[10000]'))->toBeTrue();
    expect(str_contains($content, 'onclick="if(event.target === this) closeAddFieldDrawer()"'))->toBeTrue();
});

test('UI fixes: index view contains active entity badge and Escape key event listener', function () {
    $indexPath = base_path('packages/Webkul/Moldable/src/Resources/views/builder/index.blade.php');
    $content = file_get_contents($indexPath);

    expect(str_contains($content, 'id="selected-entity-badge"'))->toBeTrue();
    expect(str_contains($content, "e.key === 'Escape'"))->toBeTrue();
});
