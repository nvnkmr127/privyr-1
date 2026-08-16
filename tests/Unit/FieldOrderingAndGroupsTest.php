<?php

use Webkul\Moldable\Models\FieldGroup;
use Webkul\Moldable\Models\FieldGroupAttribute;

test('field groups model exists with separate presentation layer mapping', function () {
    $group = new FieldGroup([
        'workspace_id' => 1,
        'entity_type' => 'leads',
        'name' => 'Property Details',
        'slug' => 'property-details',
        'sort_order' => 0,
    ]);

    expect($group->name)->toBe('Property Details');
    expect($group->slug)->toBe('property-details');
    expect($group->getTable())->toBe('moldable_field_groups');
});

test('field group attribute pivot model maps group to attribute without duplicating attribute data', function () {
    $groupAttr = new FieldGroupAttribute([
        'group_id' => 10,
        'attribute_id' => 42,
        'sort_order' => 2,
    ]);

    expect($groupAttr->group_id)->toBe(10);
    expect($groupAttr->attribute_id)->toBe(42);
    expect($groupAttr->sort_order)->toBe(2);
    expect($groupAttr->getTable())->toBe('moldable_field_group_attributes');
});

test('builder view contains drag and drop handles and field groups presentation bar', function () {
    $viewPath = base_path('packages/Webkul/Moldable/src/Resources/views/builder/index.blade.php');
    $content = file_get_contents($viewPath);

    expect(str_contains($content, 'draggable="true"'))->toBeTrue();
    expect(str_contains($content, 'handleDragStart'))->toBeTrue();
    expect(str_contains($content, 'handleDrop'))->toBeTrue();
    expect(str_contains($content, 'previousDOMState'))->toBeTrue(); // Optimistic UI rollback check

    $sampleGroups = [
        'Property Details',
        'Customer Details',
        'Project Details',
        'Financial Details',
        'Follow-up Details',
    ];

    foreach ($sampleGroups as $grp) {
        expect(str_contains($content, $grp))
            ->toBeTrue("View should present group '{$grp}'");
    }
});
