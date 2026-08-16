<?php

use Webkul\Moldable\Services\FieldTypeRegistry;

test('field type registry contains all 15 field types with complete metadata contract', function () {
    $types = FieldTypeRegistry::all();

    $expectedKeys = [
        'text', 'textarea', 'price', 'boolean', 'select', 'multiselect',
        'checkbox', 'email', 'phone', 'address', 'lookup', 'date',
        'datetime', 'file', 'image',
    ];

    expect(array_keys($types))->toEqualCanonicalizing($expectedKeys);

    $requiredProperties = [
        'key', 'label', 'icon', 'storage_type',
        'supports_options', 'supports_validation', 'supports_lookup',
        'supports_required', 'supports_unique', 'supports_quick_add',
    ];

    foreach ($types as $key => $definition) {
        foreach ($requiredProperties as $prop) {
            expect(array_key_exists($prop, $definition))
                ->toBeTrue("Property '{$prop}' missing in field type '{$key}'");
        }
    }
});
