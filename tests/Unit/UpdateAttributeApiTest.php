<?php

use Webkul\Attribute\Models\Attribute;

test('system attributes cannot be deleted', function () {
    $systemAttribute = new Attribute([
        'code' => 'title',
        'name' => 'Lead Title',
        'type' => 'text',
        'entity_type' => 'leads',
        'is_user_defined' => false,
    ]);

    expect($systemAttribute->is_user_defined)->toBeFalse();
});

test('system attributes cannot be modified', function () {
    $systemAttribute = new Attribute([
        'code' => 'title',
        'is_user_defined' => false,
    ]);

    expect($systemAttribute->is_user_defined)->toBeFalse();
});

test('entity_type and code remain immutable during field update', function () {
    $updateData = [
        'name' => 'Updated Field Name',
        'code' => 'hacked_code',
        'entity_type' => 'hacked_entity',
    ];

    unset($updateData['code'], $updateData['entity_type']);

    expect(array_key_exists('code', $updateData))->toBeFalse();
    expect(array_key_exists('entity_type', $updateData))->toBeFalse();
    expect($updateData['name'])->toBe('Updated Field Name');
});
