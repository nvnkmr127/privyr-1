<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Webkul\Lead\DataTransferObjects\LeadIngestionPayload;
use Webkul\Lead\Exceptions\LeadIngestionException;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Services\LeadIngestionService;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed');
    $this->ingestionService = app(LeadIngestionService::class);
});

it('can ingest a valid lead', function () {
    Event::fake();

    $payload = LeadIngestionPayload::fromArray([
        'origin' => 'api',
        'lead_data' => [
            'person_name' => 'John Doe',
            'emails' => [['value' => 'john@example.com', 'label' => 'work']],
            'contact_numbers' => [['value' => '+1234567890', 'label' => 'mobile']],
            'title' => 'New API Lead',
        ],
    ]);

    $lead = $this->ingestionService->ingest($payload);

    expect($lead)->toBeInstanceOf(Lead::class)
        ->and($lead->title)->toBe('New API Lead')
        ->and($lead->origin)->toBe('api')
        ->and($lead->ingestion_status)->toBe('Created');

    $this->assertDatabaseHas('lead_capture_logs', [
        'lead_id' => $lead->id,
        'status' => 'Created',
    ]);

    Event::assertDispatched('lead.ingestion.received');
    Event::assertDispatched('lead.ingestion.validated');
    Event::assertDispatched('lead.ingestion.created');
});

it('rejects duplicate email and creates log', function () {
    // Create initial lead
    $this->ingestionService->ingest(LeadIngestionPayload::fromArray([
        'origin' => 'manual',
        'lead_data' => [
            'person_name' => 'Jane Smith',
            'emails' => [['value' => 'jane@example.com', 'label' => 'work']],
        ],
    ]));

    // Attempt duplicate
    $payload = LeadIngestionPayload::fromArray([
        'origin' => 'api',
        'lead_data' => [
            'person_name' => 'Jane Smith Duplicate',
            'emails' => [['value' => 'jane@example.com', 'label' => 'work']],
        ],
    ]);

    expect(fn () => $this->ingestionService->ingest($payload))
        ->toThrow(LeadIngestionException::class);

    $this->assertDatabaseHas('lead_capture_logs', [
        'status' => 'Rejected',
        'error_message' => 'Duplicate lead detected.',
    ]);
});

it('updates duplicate lead when duplicateAction is update', function () {
    // Create initial lead
    $initialLead = $this->ingestionService->ingest(LeadIngestionPayload::fromArray([
        'origin' => 'manual',
        'lead_data' => [
            'person_name' => 'Bob Builder',
            'emails' => [['value' => 'bob@example.com', 'label' => 'work']],
            'title' => 'Initial Title',
        ],
    ]));

    // Attempt duplicate with update
    $payload = LeadIngestionPayload::fromArray([
        'origin' => 'api',
        'duplicate_action' => 'update',
        'lead_data' => [
            'title' => 'Updated Title',
            'emails' => [['value' => 'bob@example.com', 'label' => 'work']],
        ],
    ]);

    $updatedLead = $this->ingestionService->ingest($payload);

    expect($updatedLead->id)->toBe($initialLead->id)
        ->and($updatedLead->title)->toBe('Updated Title');

    $this->assertDatabaseHas('lead_capture_logs', [
        'status' => 'Updated',
        'lead_id' => $initialLead->id,
    ]);
});

it('skips duplicate lead when duplicateAction is skip', function () {
    // Create initial lead
    $initialLead = $this->ingestionService->ingest(LeadIngestionPayload::fromArray([
        'origin' => 'manual',
        'lead_data' => [
            'person_name' => 'Alice Wonder',
            'emails' => [['value' => 'alice@example.com', 'label' => 'work']],
            'title' => 'Initial Title',
        ],
    ]));

    // Attempt duplicate with skip
    $payload = LeadIngestionPayload::fromArray([
        'origin' => 'api',
        'duplicate_action' => 'skip',
        'lead_data' => [
            'title' => 'Updated Title',
            'emails' => [['value' => 'alice@example.com', 'label' => 'work']],
        ],
    ]);

    $result = $this->ingestionService->ingest($payload);

    expect($result->id)->toBe($initialLead->id);

    // Ensure title was NOT updated
    $freshLead = Lead::find($initialLead->id);
    expect($freshLead->title)->toBe('Initial Title');

    $this->assertDatabaseHas('lead_capture_logs', [
        'status' => 'Duplicate Skipped',
        'lead_id' => $initialLead->id,
    ]);
});

it('handles idempotent external ID safely', function () {
    // Create initial lead
    $initialLead = $this->ingestionService->ingest(LeadIngestionPayload::fromArray([
        'origin' => 'meta',
        'external_id' => 'ext-123',
        'lead_data' => [
            'person_name' => 'Idempotent User',
        ],
    ]));

    // Attempt exact same external ID and origin
    $payload = LeadIngestionPayload::fromArray([
        'origin' => 'meta',
        'external_id' => 'ext-123',
        'lead_data' => [
            'person_name' => 'Idempotent User',
        ],
    ]);

    $result = $this->ingestionService->ingest($payload);

    expect($result->id)->toBe($initialLead->id);

    $this->assertDatabaseHas('lead_capture_logs', [
        'status' => 'Duplicate', // idempotent duplicate
        'lead_id' => $initialLead->id,
    ]);
});
