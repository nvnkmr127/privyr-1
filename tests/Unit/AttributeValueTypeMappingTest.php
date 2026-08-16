<?php

use Webkul\Attribute\Models\AttributeValue;

test('attribute value type mapping covers all dynamic field types', function () {
    $expectedMapping = [
        'text' => 'text_value',
        'textarea' => 'text_value',
        'price' => 'float_value',
        'boolean' => 'boolean_value',
        'select' => 'integer_value',
        'multiselect' => 'text_value',
        'email' => 'json_value',
        'phone' => 'json_value',
        'address' => 'json_value',
        'date' => 'date_value',
        'datetime' => 'datetime_value',
        'lookup' => 'integer_value',
        'file' => 'text_value',
        'image' => 'text_value',
    ];

    foreach ($expectedMapping as $type => $column) {
        expect(AttributeValue::$attributeTypeFields[$type] ?? null)
            ->toBe($column, "Attribute type '{$type}' should map to column '{$column}'");
    }
});
