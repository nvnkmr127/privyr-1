<?php

use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Repositories\LeadRepository;
use Webkul\Lead\Services\LeadDataQualityService;

uses(TestCase::class);

beforeEach(function () {
    $this->dataQualityService = app(LeadDataQualityService::class);
});

it('normalizes phone and email correctly', function () {
    $data = [
        'person_name' => '  John   Doe  ',
        'emails' => [['value' => ' JOHN.DOE@example.com ', 'label' => 'work']],
        'contact_numbers' => [['value' => ' +1 (555) 123-4567 ', 'label' => 'work']],
        'title' => '<h1>New Lead</h1>',
    ];

    $normalized = $this->dataQualityService->normalize($data);

    expect($normalized['person_name'])->toBe('John Doe')
        ->and($normalized['emails'][0]['value'])->toBe('john.doe@example.com')
        ->and($normalized['normalized_primary_email'])->toBe('john.doe@example.com')
        ->and($normalized['contact_numbers'][0]['value'])->toBe('+15551234567')
        ->and($normalized['normalized_primary_phone'])->toBe('+15551234567')
        ->and($normalized['title'])->toBe('New Lead');
});

it('calculates data quality state as complete when all required fields are present', function () {
    Event::fake(['lead.data_quality.changed']);

    // Create a lead with all necessary fields
    $lead = app(LeadRepository::class)->create([
        'entity_type' => 'leads',
        'person_name' => 'Valid Name',
        'lead_source_id' => 1,
        'user_id' => 1,
        'emails' => [['value' => 'valid@example.com', 'label' => 'work']],
        'contact_numbers' => [['value' => '+15551234567', 'label' => 'work']],
    ]);

    $this->dataQualityService->calculateQualityState($lead);

    expect($lead->data_quality_state)->toBe('complete')
        ->and($lead->data_quality_issues)->toBeEmpty();

    Event::assertDispatched('lead.data_quality.changed');
});

it('calculates data quality state as needs_review for missing some fields', function () {
    $lead = app(LeadRepository::class)->create([
        'entity_type' => 'leads',
        'person_name' => 'Valid Name',
        'lead_source_id' => null, // Missing source
        'user_id' => 1,
        'emails' => [['value' => 'valid@example.com', 'label' => 'work']],
        // Missing phone
    ]);

    $this->dataQualityService->calculateQualityState($lead);

    expect($lead->data_quality_state)->toBe('needs_review')
        ->and($lead->data_quality_issues)->toContain('Missing phone number', 'Missing source');
});

it('calculates data quality state as incomplete for severely missing fields', function () {
    $lead = app(LeadRepository::class)->create([
        'entity_type' => 'leads',
        'person_name' => null,
        'lead_source_id' => null,
        'user_id' => null,
    ]);

    $this->dataQualityService->calculateQualityState($lead);

    expect($lead->data_quality_state)->toBe('incomplete')
        ->and($lead->data_quality_issues)->toContain('Missing person name', 'Missing owner', 'Missing source');
});
