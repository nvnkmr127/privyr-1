<?php

use Illuminate\Support\Str;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Models\LeadSourceConnector;
use Webkul\Moldable\Models\Workspace;
use Webkul\Moldable\Models\WorkspaceMember;

/*
|--------------------------------------------------------------------------
| Tenant isolation for the Lead Capture Integrations system.
|--------------------------------------------------------------------------
| Tenant = Moldable workspace. The current tenant is resolved by the
| ResolveWorkspace middleware from the authenticated session + a validated
| X-Workspace-Id header (membership enforced). Tests hit the live DB (no
| RefreshDatabase) so they create isolated workspaces and assert on their rows.
*/

function makeWorkspace(int $userId): Workspace
{
    $ws = Workspace::create([
        'name' => 'WS '.Str::random(6),
        'slug' => 'ws-'.Str::lower(Str::random(8)),
        'settings' => [],
        'is_active' => true,
    ]);

    WorkspaceMember::create([
        'workspace_id' => $ws->id,
        'user_id' => $userId,
        'role' => 'owner',
        'permissions' => ['*'],
        'is_active' => true,
    ]);

    return $ws;
}

function connectorIn(Workspace $ws, array $overrides = []): LeadSourceConnector
{
    return LeadSourceConnector::create(array_merge([
        'workspace_id' => $ws->id,
        'name' => 'Conn '.Str::random(5),
        'source_type' => 'webform',
        'webhook_token' => Str::random(32),
        'duplicate_action' => 'update',
        'is_active' => true,
    ], $overrides));
}

it('creates a connector owned by the current tenant (from the session, not the body)', function () {
    $admin = getDefaultAdmin();
    $ws = makeWorkspace($admin->getAuthIdentifier());

    $res = $this->actingAs($admin, 'user')
        ->withHeader('X-Workspace-Id', $ws->id)
        ->postJson(route('admin.lead_capture.integrations.store'), [
            'name' => 'Website',
            'source_type' => 'webform',
            // A malicious workspace_id in the body must be ignored.
            'workspace_id' => 999999,
        ]);

    $res->assertOk();
    $connector = LeadSourceConnector::find($res->json('connector.id'));
    expect($connector->workspace_id)->toBe($ws->id);
});

it('lists only the current tenant’s connectors', function () {
    $admin = getDefaultAdmin();
    $a = makeWorkspace($admin->getAuthIdentifier());
    $b = makeWorkspace($admin->getAuthIdentifier());
    $ca = connectorIn($a, ['name' => 'A only']);
    connectorIn($b, ['name' => 'B only']);

    $res = $this->actingAs($admin, 'user')
        ->withHeader('X-Workspace-Id', $a->id)
        ->get(route('admin.lead_capture.integrations'));

    $res->assertOk()->assertSee('A only')->assertDontSee('B only');
});

it('blocks cross-tenant test and disconnect (404)', function () {
    $admin = getDefaultAdmin();
    $a = makeWorkspace($admin->getAuthIdentifier());
    $b = makeWorkspace($admin->getAuthIdentifier());
    $ca = connectorIn($a);

    // Acting inside tenant B, tenant A's connector must be invisible.
    $this->actingAs($admin, 'user')->withHeader('X-Workspace-Id', $b->id)
        ->postJson(route('admin.lead_capture.integrations.test', $ca->id))
        ->assertStatus(404);

    $this->actingAs($admin, 'user')->withHeader('X-Workspace-Id', $b->id)
        ->postJson(route('admin.lead_capture.integrations.disconnect', $ca->id))
        ->assertStatus(404);

    expect($ca->fresh()->is_active)->toBeTrue(); // untouched
});

it('rejects a workspace the user is not a member of (403)', function () {
    $admin = getDefaultAdmin();
    $a = makeWorkspace($admin->getAuthIdentifier());
    $ca = connectorIn($a);

    // A real workspace the admin is NOT a member of.
    $foreign = Workspace::create(['name' => 'Foreign', 'slug' => 'foreign-'.Str::lower(Str::random(6)), 'settings' => [], 'is_active' => true]);

    $this->actingAs($admin, 'user')->withHeader('X-Workspace-Id', $foreign->id)
        ->postJson(route('admin.lead_capture.integrations.test', $ca->id))
        ->assertStatus(403);
});

it('stamps captured leads and contacts with the connector’s tenant, isolating dedup', function () {
    $admin = getDefaultAdmin();
    $a = makeWorkspace($admin->getAuthIdentifier());
    $b = makeWorkspace($admin->getAuthIdentifier());
    $ca = connectorIn($a);
    $cb = connectorIn($b);

    $email = 'shared.'.Str::random(5).'@example.com';
    $body = ['name' => 'Shared', 'email' => $email, 'phone_number' => '+1 555 0'.rand(1000, 9999)];

    // Same contact submitted to both tenants via their public tokens.
    $this->postJson(route('api.v1.lead_capture.webhook', ['token' => $ca->webhook_token]), $body)->assertStatus(201);
    $this->postJson(route('api.v1.lead_capture.webhook', ['token' => $cb->webhook_token]), $body)->assertStatus(201);

    $leadA = Lead::where('workspace_id', $a->id)->latest()->first();
    $leadB = Lead::where('workspace_id', $b->id)->latest()->first();

    expect($leadA)->not->toBeNull()
        ->and($leadB)->not->toBeNull()
        ->and($leadA->person_id)->not->toBe($leadB->person_id) // independent contacts per tenant
        ->and($leadA->person->workspace_id)->toBe($a->id)
        ->and($leadB->person->workspace_id)->toBe($b->id);
});
