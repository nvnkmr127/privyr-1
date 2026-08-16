<?php

use App\Models\DeviceToken;
use App\Services\PushNotificationService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Webkul\Lead\Models\LeadSourceConnector;
use Webkul\Lead\Services\LeadCaptureService;

it('registers a device token for the authenticated agent', function () {
    $admin = actingAsSanctumAuthenticatedAdmin();

    $response = $this->postJson('/api/v1/device-tokens', [
        'token' => 'fcm-token-abc',
        'platform' => 'android',
        'device_name' => 'Pixel 8',
    ]);

    $response->assertCreated()->assertJsonPath('status', 'success');

    $this->assertDatabaseHas('device_tokens', [
        'user_id' => $admin->id,
        'token' => 'fcm-token-abc',
        'platform' => 'android',
        'device_name' => 'Pixel 8',
    ]);
});

it('re-registering the same token re-points it to the current agent without duplicating', function () {
    $admin = actingAsSanctumAuthenticatedAdmin();

    $this->postJson('/api/v1/device-tokens', ['token' => 'shared-token', 'platform' => 'ios']);
    $this->postJson('/api/v1/device-tokens', ['token' => 'shared-token', 'platform' => 'android']);

    expect(DeviceToken::where('token', 'shared-token')->count())->toBe(1)
        ->and(DeviceToken::where('token', 'shared-token')->first()->platform)->toBe('android');
});

it('validates the platform on registration', function () {
    actingAsSanctumAuthenticatedAdmin();

    $this->postJson('/api/v1/device-tokens', [
        'token' => 'fcm-token-xyz',
        'platform' => 'blackberry',
    ])->assertStatus(422);
});

it('unregisters a device token', function () {
    $admin = actingAsSanctumAuthenticatedAdmin();

    DeviceToken::create(['user_id' => $admin->id, 'token' => 'to-remove', 'platform' => 'android']);

    $this->deleteJson('/api/v1/device-tokens', ['token' => 'to-remove'])
        ->assertOk()
        ->assertJsonPath('status', 'success');

    $this->assertDatabaseMissing('device_tokens', ['token' => 'to-remove']);
});

it('rejects device-token endpoints without authentication', function () {
    $this->postJson('/api/v1/device-tokens', ['token' => 'nope'])->assertUnauthorized();
});

it('is a no-op when push is not configured', function () {
    config(['services.push.key' => null]);
    Http::fake();

    $result = app(PushNotificationService::class)->send(['some-token'], 'Title', 'Body');

    expect($result['sent'])->toBeFalse()
        ->and($result['reason'])->toBe('not_configured');

    Http::assertNothingSent();
});

it('treats an HTTP 200 with zero deliveries as not sent and prunes the dead tokens', function () {
    $admin = getDefaultAdmin();

    config([
        'services.push.key' => 'test-server-key',
        'services.push.endpoint' => 'https://fcm.googleapis.com/fcm/send',
    ]);

    DeviceToken::create(['user_id' => $admin->id, 'token' => 'gone-1', 'platform' => 'android']);
    DeviceToken::create(['user_id' => $admin->id, 'token' => 'gone-2', 'platform' => 'android']);

    // Accepted by FCM, but every token is unregistered — nothing was delivered.
    Http::fake([
        'fcm.googleapis.com/*' => Http::response([
            'success' => 0,
            'failure' => 2,
            'results' => [
                ['error' => 'NotRegistered'],
                ['error' => 'InvalidRegistration'],
            ],
        ], 200),
    ]);

    $sent = app(PushNotificationService::class)->sendToUser($admin, 'New Lead', 'Body');

    expect($sent)->toBeFalse();

    // Dead tokens pruned; nothing left to stamp as used.
    $this->assertDatabaseMissing('device_tokens', ['token' => 'gone-1']);
    $this->assertDatabaseMissing('device_tokens', ['token' => 'gone-2']);
});

it('dispatches a push to the agent devices and prunes dead tokens', function () {
    $admin = getDefaultAdmin();

    config([
        'services.push.key' => 'test-server-key',
        'services.push.endpoint' => 'https://fcm.googleapis.com/fcm/send',
    ]);

    DeviceToken::create(['user_id' => $admin->id, 'token' => 'live-token', 'platform' => 'android']);
    DeviceToken::create(['user_id' => $admin->id, 'token' => 'dead-token', 'platform' => 'android']);

    // FCM reports per-token results positionally: first ok, second unregistered.
    Http::fake([
        'fcm.googleapis.com/*' => Http::response([
            'success' => 1,
            'failure' => 1,
            'results' => [
                ['message_id' => '0:123'],
                ['error' => 'NotRegistered'],
            ],
        ], 200),
    ]);

    $sent = app(PushNotificationService::class)->sendToUser($admin, 'New Lead', 'Body');

    expect($sent)->toBeTrue();

    Http::assertSent(fn ($request) => str_contains($request->url(), 'fcm.googleapis.com')
        && $request['registration_ids'] === ['live-token', 'dead-token']);

    // Dead token pruned, live token kept and stamped as used.
    $this->assertDatabaseMissing('device_tokens', ['token' => 'dead-token']);
    $this->assertDatabaseHas('device_tokens', ['token' => 'live-token']);
    expect(DeviceToken::where('token', 'live-token')->first()->last_used_at)->not->toBeNull();
});

it('pushes to the assigned agent when a lead is captured', function () {
    $admin = getDefaultAdmin();

    config([
        'services.push.key' => 'test-server-key',
        'services.push.endpoint' => 'https://fcm.googleapis.com/fcm/send',
    ]);

    Http::fake([
        'fcm.googleapis.com/*' => Http::response(['success' => 1, 'results' => [['message_id' => '0:1']]], 200),
    ]);

    DeviceToken::create(['user_id' => $admin->id, 'token' => 'agent-device', 'platform' => 'android']);

    $connector = LeadSourceConnector::create([
        'name' => 'Push Test Connector',
        'source_type' => 'webhook',
        'webhook_token' => Str::random(32),
        'duplicate_action' => 'update',
        'default_user_id' => $admin->id,
        'is_active' => true,
    ]);

    app(LeadCaptureService::class)->processIncomingPayload($connector, [
        'full_name' => 'Priya Nair',
        'email' => 'priya.nair@example.com',
        'phone' => '+91 9000000001',
    ]);

    Http::assertSent(fn ($request) => str_contains($request->url(), 'fcm.googleapis.com')
        && $request['registration_ids'] === ['agent-device']);
});
