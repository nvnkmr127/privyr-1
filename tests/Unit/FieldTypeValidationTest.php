<?php

use Illuminate\Support\Facades\Validator;
use Webkul\Moldable\Services\FieldTypeRegistry;

test('field type registry rejects unsupported field types', function () {
    $unsupportedTypes = ['invalid', 'matrix', 'formula', 'custom_blob', 'script'];

    foreach ($unsupportedTypes as $type) {
        expect(FieldTypeRegistry::isValid($type))->toBeFalse();

        $validator = Validator::make(
            ['type' => $type],
            ['type' => ['required', FieldTypeRegistry::validationRule()]]
        );

        expect($validator->fails())->toBeTrue("Validation should fail for unsupported type '{$type}'");
    }
});

test('field type registry accepts all supported field types', function () {
    foreach (FieldTypeRegistry::keys() as $type) {
        expect(FieldTypeRegistry::isValid($type))->toBeTrue();

        $validator = Validator::make(
            ['type' => $type],
            ['type' => ['required', FieldTypeRegistry::validationRule()]]
        );

        expect($validator->passes())->toBeTrue("Validation should pass for supported type '{$type}'");
    }
});
