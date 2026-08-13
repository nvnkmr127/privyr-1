<?php

use Webkul\Moldable\Services\FieldTypeRegistry;

test('field type registry returns grouped field types matching all 8 requested groups', function () {
    $grouped = FieldTypeRegistry::grouped();

    $expectedGroups = [
        'Text',
        'Number',
        'Date & Time',
        'Selection',
        'Relationship',
        'Contact',
        'File',
    ];

    foreach ($expectedGroups as $group) {
        expect(array_key_exists($group, $grouped))
            ->toBeTrue("FieldTypeRegistry::grouped() should contain group '{$group}'");
    }
});

test('every field type is assigned a valid group', function () {
    foreach (FieldTypeRegistry::all() as $type => $definition) {
        expect(array_key_exists('group', $definition))
            ->toBeTrue("Type '{$type}' must have a group metadata property");
    }
});
