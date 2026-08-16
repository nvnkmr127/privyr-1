<?php

use Illuminate\Support\Str;

test('generates stable code from display name', function () {
    $name = 'Project Budget';
    $code = Str::limit(Str::snake($name), 110, '');

    expect($code)->toBe('project_budget');
});

test('appends incrementing numerical suffix when code exists for entity', function () {
    $slug = 'project_budget';
    $existingCodes = ['project_budget'];

    $code = $slug;
    $counter = 2;
    while (in_array($code, $existingCodes)) {
        $code = $slug.'_'.$counter;
        $counter++;
    }

    expect($code)->toBe('project_budget_2');

    $existingCodes[] = 'project_budget_2';
    $code2 = $slug;
    $counter2 = 2;
    while (in_array($code2, $existingCodes)) {
        $code2 = $slug.'_'.$counter2;
        $counter2++;
    }

    expect($code2)->toBe('project_budget_3');
});

test('code is preserved and never regenerated when editing name', function () {
    $attributeData = [
        'code' => 'original_code',
        'name' => 'Original Name',
        'entity_type' => 'leads',
    ];

    $updatePayload = [
        'name' => 'Completely Different Name',
    ];

    unset($updatePayload['code']);

    $merged = array_merge($attributeData, $updatePayload);

    expect($merged['code'])->toBe('original_code');
    expect($merged['name'])->toBe('Completely Different Name');
});
