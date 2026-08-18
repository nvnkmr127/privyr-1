<?php

use Illuminate\Support\Str;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Models\LeadCaptureLog;
use Webkul\Lead\Models\LeadSourceConnector;
use Webkul\Moldable\Models\Workspace;

/*
|--------------------------------------------------------------------------
| Source-specific integration flows for /admin/lead-capture/integrations
|--------------------------------------------------------------------------
| NOTE: this project's tests run against the live MySQL connection (no
| RefreshDatabase). Each test uses unique tokens/emails and asserts on the
| rows it created so it does not depend on a clean database.
*/

function makeConnector(array $overrides = []): LeadSourceConnector
{
    return LeadSourceConnector::create(array_merge([
        'name' => 'Test '.Str::random(6),
        'source_type' => 'webhook',
        'webhook_token' => Str::random(32),
        'duplicate_action' => 'update',
        'is_active' => true,
    ], $overrides));
}

it('creates a webhook connector with the real source_type and returns a live url', function () {
    $this->actingAs(getDefaultAdmin(), 'user');

    $response = $this->postJson(route('admin.lead_capture.integrations.store'), [
        'name' => 'My Zapier Bridge',
        'source_type' => 'webhook',
        'duplicate_action' => 'update',
        'mapping' => ['full_name' => 'person.name'],
    ]);

    $response->assertOk()
        ->assertJsonPath('connector.source_type', 'webhook')
        ->assertJsonPath('connector.is_active', true);

    expect($response->json('connector.webhook_url'))->toContain('/api/v1/lead-capture/webhook/');

    $this->assertDatabaseHas('lead_source_connectors', [
        'id' => $response->json('connector.id'),
        'source_type' => 'webhook',
    ]);
});

it('persists embed_config for embed sources', function () {
    $this->actingAs(getDefaultAdmin(), 'user');

    $response = $this->postJson(route('admin.lead_capture.integrations.store'), [
        'name' => 'Site Form',
        'source_type' => 'webform',
        'embed_config' => [
            'title' => 'Get a Quote',
            'button_text' => 'Send',
            'fields' => ['email' => true, 'phone' => true, 'message' => false],
        ],
    ]);

    $response->assertOk();
    $connector = LeadSourceConnector::find($response->json('connector.id'));

    expect($connector->embed_config['title'])->toBe('Get a Quote')
        ->and($connector->embed_config['button_text'])->toBe('Send');
});

it('rejects an unknown source_type', function () {
    $this->actingAs(getDefaultAdmin(), 'user');

    $this->postJson(route('admin.lead_capture.integrations.store'), [
        'name' => 'Bad',
        'source_type' => 'not_a_real_source',
    ])->assertStatus(422);
});

it('requires authentication to create a connector', function () {
    $this->postJson(route('admin.lead_capture.integrations.store'), [
        'name' => 'Anon',
        'source_type' => 'webhook',
    ])->assertRedirect(); // Bouncer redirects unauthenticated requests to login
});

it('serves a real embed loader script for active tokens and an inert one otherwise', function () {
    $connector = makeConnector(['source_type' => 'webform']);

    $ok = $this->get(route('public.lead_capture.embed_js', ['token' => $connector->webhook_token]));
    $ok->assertOk();
    expect($ok->getContent())->toContain($connector->webhook_token)
        ->and($ok->getContent())->toContain('iframe');

    $inert = $this->get(route('public.lead_capture.embed_js', ['token' => 'nope']));
    $inert->assertOk();
    expect($inert->getContent())->toContain('unknown or inactive');
});

it('runs the test action as a dry run without persisting a lead', function () {
    $this->actingAs(getDefaultAdmin(), 'user');

    $workspaceId = Workspace::first()->id;
    $connector = makeConnector(['workspace_id' => $workspaceId]);
    $leadsBefore = Lead::count();
    $logsBefore = LeadCaptureLog::where('connector_id', $connector->id)->count();

    $response = $this->withHeader('X-Workspace-Id', (string) $workspaceId)
        ->postJson(route('admin.lead_capture.integrations.test', $connector->id), [
            'payload' => ['name' => 'Dry Run', 'email' => 'dry.run@example.com', 'phone' => '+1 555 9'],
        ]);

    $response->assertOk()
        ->assertJsonPath('preview.dry_run', true)
        ->assertJsonPath('preview.would_create_lead', true);

    expect(Lead::count())->toBe($leadsBefore)
        ->and(LeadCaptureLog::where('connector_id', $connector->id)->count())->toBe($logsBefore);
});

it('routes webhook payloads to the correct connector in isolation', function () {
    $a = makeConnector(['name' => 'Source A']);
    $b = makeConnector(['name' => 'Source B']);

    $this->postJson(route('api.v1.lead_capture.webhook', ['token' => $a->webhook_token]), [
        'name' => 'Alice A', 'email' => 'alice.'.Str::random(4).'@example.com', 'phone' => '+1 555 1',
    ])->assertStatus(201);

    $this->postJson(route('api.v1.lead_capture.webhook', ['token' => $b->webhook_token]), [
        'name' => 'Bob B', 'email' => 'bob.'.Str::random(4).'@example.com', 'phone' => '+1 555 2',
    ])->assertStatus(201);

    // Each connector logged exactly its own lead — no cross-contamination.
    expect(LeadCaptureLog::where('connector_id', $a->id)->where('status', 'success')->count())->toBe(1)
        ->and(LeadCaptureLog::where('connector_id', $b->id)->where('status', 'success')->count())->toBe(1);

    $aLog = LeadCaptureLog::where('connector_id', $a->id)->latest()->first();
    expect(Lead::find($aLog->lead_id)->person_name)->toBe('Alice A');
});

it('rejects webhooks for an inactive connector', function () {
    $connector = makeConnector(['is_active' => false]);

    $this->postJson(route('api.v1.lead_capture.webhook', ['token' => $connector->webhook_token]), [
        'name' => 'Nobody', 'email' => 'no@example.com',
    ])->assertStatus(404);
});
