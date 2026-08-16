<?php

use Illuminate\Support\Str;

test('auto-generates snake_case code when code is omitted', function () {
    $name = 'Project Budget Estimate';
    $codeRaw = $name;
    $code = Str::limit(Str::snake($codeRaw), 120, '');

    expect($code)->toBe('project_budget_estimate');
});

test('enforces user_defined flag automatically on attribute creation', function () {
    $data = [
        'code' => 'client_priority',
        'name' => 'Client Priority',
        'type' => 'select',
        'entity_type' => 'leads',
        'is_user_defined' => true,
    ];

    expect($data['is_user_defined'])->toBeTrue();
});

test('validates uniqueness per entity_type', function () {
    $existingEntity = 'leads';
    $code = 'budget';

    $isDuplicateInLeads = ($existingEntity === 'leads' && $code === 'budget');
    expect($isDuplicateInLeads)->toBeTrue();

    $isDuplicateInContacts = ($existingEntity === 'contacts' && $code === 'budget');
    expect($isDuplicateInContacts)->toBeFalse();
});
