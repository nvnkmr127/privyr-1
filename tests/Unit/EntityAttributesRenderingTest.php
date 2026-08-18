<?php

use Webkul\Attribute\Models\Attribute;
use Webkul\Lead\Models\Lead;

test('MOLD-023: Lead custom attributes resolve correctly on Lead entity', function () {
    $attribute = Attribute::create([
        'code' => 'project_budget_test',
        'name' => 'Project Budget Test',
        'type' => 'price',
        'entity_type' => 'leads',
        'is_user_defined' => true,
    ]);

    $lead = new Lead;
    $lead->title = 'Test Lead for Custom Attribute';
    $lead->status = 1;
    $lead->save();

    $customAttrs = $lead->getCustomAttributes();
    expect($customAttrs->pluck('code')->toArray())->toContain('project_budget_test');

    $attribute->delete();
    $lead->delete();
});
