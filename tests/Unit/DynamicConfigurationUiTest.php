<?php

test('drawer configuration section contains dynamic configuration blocks for select, lookup, text, and file field types', function () {
    $viewPath = base_path('packages/Webkul/Moldable/src/Resources/views/builder/drawer.blade.php');
    $content  = file_get_contents($viewPath);

    $requiredConfigBlocks = [
        'options-config-container',
        'lookup-config-container',
        'text-config-container',
        'file-config-container',
        'Allowed Extensions',
        'Maximum Size (MB)',
        'Validation Regex Pattern',
        'Lookup Entity Model',
    ];

    foreach ($requiredConfigBlocks as $block) {
        expect(str_contains($content, $block))
            ->toBeTrue("Add Field Drawer should contain dynamic configuration element '{$block}'");
    }
});
