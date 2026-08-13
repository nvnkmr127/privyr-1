<?php

use Illuminate\Support\Str;
use Webkul\Lead\Models\LeadSourceConnector;
use Webkul\Lead\Services\LeadCaptureService;

it('creates a lead source connector', function () {
    $this->loginAsAdmin();

    $response = $this->post(route('admin.settings.lead_connectors.store'), [
        'name' => 'Meta Lead Ads Connector',
        'source_type' => 'meta_ads',
        'duplicate_action' => 'update',
    ]);

    $response->assertRedirect(route('admin.settings.lead_connectors.index'));
    $this->assertDatabaseHas('lead_source_connectors', [
        'name' => 'Meta Lead Ads Connector',
        'source_type' => 'meta_ads',
    ]);
});

it('processes incoming webhook payload and auto maps fields to create lead and person', function () {
    $connector = LeadSourceConnector::create([
        'name' => 'IndiaMART Direct Connector',
        'source_type' => 'indiamart',
        'webhook_token' => Str::random(32),
        'duplicate_action' => 'update',
        'is_active' => true,
    ]);

    $payload = [
        'SENDER_NAME' => 'Rajesh Sharma',
        'SENDER_EMAIL' => 'rajesh.sharma@example.com',
        'SENDER_MOBILE' => '+91 9876543210',
        'QUERY_PRODUCT_NAME' => 'Industrial Machinery',
        'MAX_BUDGET' => 500000,
    ];

    /** @var LeadCaptureService $service */
    $service = app(LeadCaptureService::class);
    $lead = $service->processIncomingPayload($connector, $payload);

    expect($lead)->not->toBeNull()
        ->and($lead->title)->toContain('Industrial Machinery')
        ->and($lead->person->name)->toBe('Rajesh Sharma');

    $this->assertDatabaseHas('lead_capture_logs', [
        'connector_id' => $connector->id,
        'status' => 'success',
        'lead_id' => $lead->id,
    ]);
});

it('handles duplicate detection correctly', function () {
    /** @var LeadCaptureService $service */
    $service = app(LeadCaptureService::class);

    $connector = LeadSourceConnector::create([
        'name' => 'Duplicate Test Connector',
        'source_type' => 'webhook',
        'webhook_token' => Str::random(32),
        'duplicate_action' => 'skip',
        'is_active' => true,
    ]);

    // First Payload
    $service->processIncomingPayload($connector, [
        'full_name' => 'Amit Verma',
        'email' => 'amit.verma@example.com',
        'phone' => '+91 9123456789',
    ]);

    // Second Payload with same email (Duplicate)
    $secondLead = $service->processIncomingPayload($connector, [
        'full_name' => 'Amit Verma Duplicate',
        'email' => 'amit.verma@example.com',
        'phone' => '+91 9123456789',
    ]);

    expect($secondLead)->toBeNull();
});
