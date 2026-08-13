<?php

use Webkul\Attribute\Models\Attribute;

test('is_unique and quick_add attributes exist on Attribute fillable and are cast to boolean', function () {
    $attribute = new Attribute([
        'is_unique' => 1,
        'quick_add' => 1,
    ]);

    expect(in_array('is_unique', $attribute->getFillable()))->toBeTrue();
    expect(in_array('quick_add', $attribute->getFillable()))->toBeTrue();

    expect($attribute->is_unique)->toBeTrue();
    expect($attribute->quick_add)->toBeTrue();
});

test('drawer view contains is_unique and quick_add input elements connected directly to properties', function () {
    $viewPath = base_path('packages/Webkul/Moldable/src/Resources/views/builder/drawer.blade.php');
    $content  = file_get_contents($viewPath);

    expect(str_contains($content, 'name="is_unique"'))->toBeTrue();
    expect(str_contains($content, 'id="is_unique"'))->toBeTrue();

    expect(str_contains($content, 'name="quick_add"'))->toBeTrue();
    expect(str_contains($content, 'id="quick_add"'))->toBeTrue();
});
