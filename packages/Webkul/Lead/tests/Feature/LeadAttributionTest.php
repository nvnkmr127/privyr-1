<?php

use Illuminate\Support\Facades\Event;
use Webkul\Lead\DataTransferObjects\LeadIngestionPayload;
use Webkul\Lead\Models\Source;
use Webkul\Lead\Services\LeadIngestionService;

it('preserves first touch attribution on update', function () {
    Event::fake([
        'lead.attribution.updated',
    ]);

    $source = Source::factory()->create(['name' => 'Initial Source']);
    $newSource = Source::factory()->create(['name' => 'Updated Source']);

    $ingestionService = app(LeadIngestionService::class);

    // Initial creation
    $payload1 = new LeadIngestionPayload(
        connectorId: 1,
        origin: 'webform',
        leadData: [
            'title' => 'Test Lead',
            'person_name' => 'Test Person',
            'emails' => [['value' => 'test@example.com', 'label' => 'work']],
        ],
        sourceId: $source->id,
        externalId: 'ext-123',
        metadata: ['campaign' => 'Initial Campaign']
    );

    $lead = $ingestionService->ingest($payload1);

    expect($lead->first_origin)->toBe('webform')
        ->and($lead->first_lead_source_id)->toBe($source->id)
        ->and($lead->first_campaign)->toBe('Initial Campaign')
        ->and($lead->latest_origin)->toBe('webform')
        ->and($lead->latest_campaign)->toBe('Initial Campaign');

    // Update with new payload
    $payload2 = new LeadIngestionPayload(
        connectorId: 1,
        origin: 'api',
        leadData: [
            'title' => 'Test Lead',
            'person_name' => 'Test Person',
            'emails' => [['value' => 'test@example.com', 'label' => 'work']],
        ],
        sourceId: $newSource->id,
        externalId: 'ext-456',
        metadata: ['campaign' => 'New Campaign'],
        duplicateAction: 'update'
    );

    $updatedLead = $ingestionService->ingest($payload2);

    // First touch should be preserved
    expect($updatedLead->first_origin)->toBe('webform')
        ->and($updatedLead->first_lead_source_id)->toBe($source->id)
        ->and($updatedLead->first_campaign)->toBe('Initial Campaign');

    // Latest touch should be updated
    expect($updatedLead->latest_origin)->toBe('api')
        ->and($updatedLead->latest_lead_source_id)->toBe($newSource->id)
        ->and($updatedLead->latest_campaign)->toBe('New Campaign');

    // History should have 2 records
    expect($updatedLead->attributionHistories()->count())->toBe(2);
});
