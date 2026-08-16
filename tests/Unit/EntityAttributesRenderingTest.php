<?php

use Webkul\Attribute\Models\Attribute;
use Webkul\Contact\Models\Organization;
use Webkul\Contact\Models\Person;
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

test('MOLD-024: Person custom attributes resolve correctly on Person entity', function () {
    $attribute = Attribute::create([
        'code' => 'vip_status_test',
        'name' => 'VIP Status Test',
        'type' => 'boolean',
        'entity_type' => 'persons',
        'is_user_defined' => true,
    ]);

    $person = new Person;
    $person->name = 'John Doe Test';
    $person->save();

    $customAttrs = $person->getCustomAttributes();
    expect($customAttrs->pluck('code')->toArray())->toContain('vip_status_test');

    $attribute->delete();
    $person->delete();
});

test('MOLD-025: Organization custom attributes resolve correctly on Organization entity', function () {
    $attribute = Attribute::create([
        'code' => 'industry_sector_test',
        'name' => 'Industry Sector Test',
        'type' => 'text',
        'entity_type' => 'organizations',
        'is_user_defined' => true,
    ]);

    $org = new Organization;
    $org->name = 'Acme Corp Test';
    $org->save();

    $customAttrs = $org->getCustomAttributes();
    expect($customAttrs->pluck('code')->toArray())->toContain('industry_sector_test');

    $attribute->delete();
    $org->delete();
});
