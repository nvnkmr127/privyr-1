<?php

use App\Models\DeviceToken;
use App\Services\PushNotificationService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Webkul\Lead\Models\LeadSourceConnector;
use Webkul\Lead\Services\LeadCaptureService;

/**
 * Write a throwaway Firebase service-account JSON (with a real RSA key so JWT
 * signing succeeds) and point push config at it.
 */
function fakeFcmCredentials(): string
{
    $key = openssl_pkey_new([
        'private_key_bits' => 2048,
        'private_key_type' => OPENSSL_KEYTYPE_RSA,
    ]);

    openssl_pkey_export($key, $privateKeyPem);

    $path = tempnam(sys_get_temp_dir(), 'fcm').'.json';

    file_put_contents($path, json_encode([
        'type' => 'service_account',
        'project_id' => 'test-project',
        'client_email' => 'fcm@test-project.iam.gserviceaccount.com',
        'private_key' => $privateKeyPem,
        'token_uri' => 'https://oauth2.googleapis.com/token',
    ]));

    config([
        'services.push.credentials' => $path,
        'services.push.project_id' => 'test-project',
        'services.push.endpoint' => 'https://fcm.googleapis.com/v1/projects/{project}/messages:send',
    ]);

    return $path;
}

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
    config(['services.push.credentials' => null, 'services.push.project_id' => null]);
    Http::fake();

    $result = app(PushNotificationService::class)->send(['some-token'], 'Title', 'Body');

    expect($result['sent'])->toBeFalse()
        ->and($result['reason'])->toBe('not_configured');

    Http::assertNothingSent();
});

it('treats zero successful deliveries as not sent and prunes the dead tokens', function () {
    $admin = getDefaultAdmin();

    fakeFcmCredentials();

    DeviceToken::create(['user_id' => $admin->id, 'token' => 'gone-1', 'platform' => 'android']);
    DeviceToken::create(['user_id' => $admin->id, 'token' => 'gone-2', 'platform' => 'android']);

    // Token exchange succeeds, but FCM v1 reports every device as unregistered.
    Http::fake([
        'oauth2.googleapis.com/token' => Http::response(['access_token' => 'ya29.fake', 'expires_in' => 3599], 200),
        'fcm.googleapis.com/*' => Http::response([
            'error' => ['code' => 404, 'status' => 'NOT_FOUND', 'details' => [['errorCode' => 'UNREGISTERED']]],
        ], 404),
    ]);

    $sent = app(PushNotificationService::class)->sendToUser($admin, 'New Lead', 'Body');

    expect($sent['sent'])->toBeFalse();

    // Dead tokens pruned; nothing left to stamp as used.
    $this->assertDatabaseMissing('device_tokens', ['token' => 'gone-1']);
    $this->assertDatabaseMissing('device_tokens', ['token' => 'gone-2']);
});

it('dispatches a push to the agent devices and prunes dead tokens', function () {
    $admin = getDefaultAdmin();

    fakeFcmCredentials();

    DeviceToken::create(['user_id' => $admin->id, 'token' => 'live-token', 'platform' => 'android']);
    DeviceToken::create(['user_id' => $admin->id, 'token' => 'dead-token', 'platform' => 'android']);

    // v1 sends one request per token: first delivers, second is unregistered.
    Http::fake([
        'oauth2.googleapis.com/token' => Http::response(['access_token' => 'ya29.fake', 'expires_in' => 3599], 200),
        'fcm.googleapis.com/*' => Http::sequence()
            ->push(['name' => 'projects/test-project/messages/1'], 200)
            ->push(['error' => ['code' => 404, 'status' => 'NOT_FOUND', 'details' => [['errorCode' => 'UNREGISTERED']]]], 404),
    ]);

    $sent = app(PushNotificationService::class)->sendToUser($admin, 'New Lead', 'Body');

    expect($sent['sent'])->toBeTrue();

    Http::assertSent(fn ($request) => str_contains($request->url(), 'messages:send')
        && data_get($request->data(), 'message.token') === 'live-token');

    // Dead token pruned, live token kept and stamped as used.
    $this->assertDatabaseMissing('device_tokens', ['token' => 'dead-token']);
    $this->assertDatabaseHas('device_tokens', ['token' => 'live-token']);
    expect(DeviceToken::where('token', 'live-token')->first()->last_used_at)->not->toBeNull();
});

it('pushes to the assigned agent when a lead is captured', function () {
    $admin = getDefaultAdmin();

    fakeFcmCredentials();

    Http::fake([
        'oauth2.googleapis.com/token' => Http::response(['access_token' => 'ya29.fake', 'expires_in' => 3599], 200),
        'fcm.googleapis.com/*' => Http::response(['name' => 'projects/test-project/messages/1'], 200),
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

    $email = 'priya.' . \Illuminate\Support\Str::random(5) . '@example.com';
    app(LeadCaptureService::class)->processIncomingPayload($connector, [
        'full_name' => 'Priya Nair',
        'email' => $email,
        'phone' => '+91 9' . rand(100000000, 999999999),
    ]);

    Http::assertSent(fn ($request) => str_contains($request->url(), 'messages:send')
        && data_get($request->data(), 'message.token') === 'agent-device');
});
