<?php

use Webkul\Attribute\Models\Attribute;

test('is_required property exists on attribute model fillable and is cast to boolean', function () {
    $attribute = new Attribute(['is_required' => 1]);

    expect(in_array('is_required', $attribute->getFillable()))->toBeTrue();
    expect($attribute->is_required)->toBeTrue();
});

test('drawer view contains is_required input toggle connected to is_required attribute', function () {
    $viewPath = base_path('packages/Webkul/Moldable/src/Resources/views/builder/drawer.blade.php');
    $content = file_get_contents($viewPath);

    expect(str_contains($content, 'name="is_required"'))->toBeTrue();
    expect(str_contains($content, 'id="is_required"'))->toBeTrue();
});
