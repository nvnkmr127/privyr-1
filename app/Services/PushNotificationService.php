<?php

namespace App\Services;

use App\Models\DeviceToken;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Webkul\User\Contracts\User;

class PushNotificationService
{
    /**
     * Loaded service-account credentials (false once we've looked and found none).
     *
     * @var array<string, mixed>|null|false
     */
    protected $loadedCredentials = false;

    /**
     * Send a push notification to a set of device tokens via the Firebase Cloud
     * Messaging HTTP v1 API.
     *
     * Mirrors WhatsAppService: a safe no-op (logs only) when push isn't
     * configured, so the CRM works ungated out of the box. FCM v1 sends to one
     * token per request, so delivery is tracked per token and dead tokens are
     * surfaced for pruning.
     *
     * @param  array<int, string>  $tokens
     * @param  array<string, mixed>  $data
     * @return array{sent: bool, invalid_tokens: array<int, string>, reason: string|null}
     */
    public function send(array $tokens, string $title, string $body, array $data = []): array
    {
        $tokens = array_values(array_filter(array_unique($tokens)));

        if (empty($tokens)) {
            return ['sent' => false, 'invalid_tokens' => [], 'reason' => 'no_tokens'];
        }

        $accessToken = $this->accessToken();
        $projectId = $this->projectId();

        if (! $accessToken || ! $projectId) {
            Log::info('Push not configured; new-lead push skipped for '.count($tokens).' device(s).');

            return ['sent' => false, 'invalid_tokens' => [], 'reason' => 'not_configured'];
        }

        $endpoint = str_replace('{project}', $projectId, (string) config('services.push.endpoint'));

        $successCount = 0;
        $invalid = [];

        foreach ($tokens as $token) {
            $response = Http::withToken($accessToken)->post($endpoint, [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    // FCM v1 data values must be strings.
                    'data' => array_map(fn ($value) => (string) $value, $data),
                    'android' => ['priority' => 'high'],
                ],
            ]);

            if ($response->successful()) {
                $successCount++;

                continue;
            }

            if ($this->isTokenUnregistered($response)) {
                $invalid[] = $token;

                continue;
            }

            Log::error('FCM v1 push failed for a device: '.$response->status().' '.$response->body());
        }

        if ($successCount === 0) {
            if (! empty($invalid)) {
                Log::warning('FCM push reached zero devices; all target tokens are unregistered.');
            }

            return ['sent' => false, 'invalid_tokens' => $invalid, 'reason' => 'no_delivery'];
        }

        return ['sent' => true, 'invalid_tokens' => $invalid, 'reason' => null];
    }

    /**
     * Push to every device registered to a given agent, pruning any tokens FCM
     * reports as unregistered and stamping the rest as freshly used.
     *
     * @param  User|object|null  $user
     * @param  array<string, mixed>  $data
     * @return array{sent: bool, reason: string|null}
     */
    public function sendToUser($user, string $title, string $body, array $data = []): array
    {
        if (! $user) {
            return ['sent' => false, 'reason' => 'no_user'];
        }

        $tokens = DeviceToken::where('user_id', $user->id)->pluck('token')->all();

        if (empty($tokens)) {
            return ['sent' => false, 'reason' => 'no_tokens'];
        }

        $result = $this->send($tokens, $title, $body, $data);

        if (! empty($result['invalid_tokens'])) {
            DeviceToken::where('user_id', $user->id)
                ->whereIn('token', $result['invalid_tokens'])
                ->delete();
        }

        if ($result['sent']) {
            DeviceToken::where('user_id', $user->id)->update(['last_used_at' => now()]);
        }

        return $result;
    }

    /**
     * Whether FCM rejected this send because the token is no longer valid, so
     * the caller should prune it.
     */
    protected function isTokenUnregistered(Response $response): bool
    {
        if ($response->status() === 404) {
            return true;
        }

        foreach ((array) $response->json('error.details', []) as $detail) {
            if (($detail['errorCode'] ?? null) === 'UNREGISTERED') {
                return true;
            }
        }

        return false;
    }

    /**
     * Mint (and cache) a short-lived OAuth2 access token for the FCM v1 API from
     * the configured service-account credentials.
     */
    protected function accessToken(): ?string
    {
        $creds = $this->credentials();

        if (! $creds) {
            return null;
        }

        $cacheKey = 'fcm_access_token_'.md5($creds['client_email']);

        // Cache below the 3600s token lifetime so we always hand out a live token.
        return Cache::remember($cacheKey, 3300, function () use ($creds) {
            $now = time();

            $jwt = $this->encodeServiceAccountJwt([
                'iss' => $creds['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => $creds['token_uri'],
                'iat' => $now,
                'exp' => $now + 3600,
            ], $creds['private_key']);

            if (! $jwt) {
                return null;
            }

            $response = Http::asForm()->post($creds['token_uri'], [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            if (! $response->successful()) {
                Log::error('FCM OAuth token exchange failed: '.$response->body());

                return null;
            }

            return $response->json('access_token');
        });
    }

    /**
     * The Firebase project id, from config or the service-account file.
     */
    protected function projectId(): ?string
    {
        return config('services.push.project_id') ?: ($this->credentials()['project_id'] ?? null);
    }

    /**
     * Load and validate the service-account JSON key file.
     *
     * @return array<string, mixed>|null
     */
    protected function credentials(): ?array
    {
        $path = config('services.push.credentials');

        if (! $path || ! is_file($path)) {
            return null;
        }

        $json = json_decode((string) file_get_contents($path), true);

        if (! is_array($json) || empty($json['client_email']) || empty($json['private_key'])) {
            Log::error('FCM service-account file is missing client_email/private_key.');

            return null;
        }

        $json['token_uri'] = $json['token_uri'] ?? 'https://oauth2.googleapis.com/token';

        return $json;
    }

    /**
     * Build a signed RS256 JWT assertion for the service-account token exchange.
     *
     * @param  array<string, mixed>  $claims
     */
    protected function encodeServiceAccountJwt(array $claims, string $privateKey): ?string
    {
        $segments = [
            $this->base64UrlEncode((string) json_encode(['alg' => 'RS256', 'typ' => 'JWT'])),
            $this->base64UrlEncode((string) json_encode($claims)),
        ];

        $signingInput = implode('.', $segments);
        $signature = '';

        if (! openssl_sign($signingInput, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            Log::error('FCM JWT signing failed; check the service-account private key.');

            return null;
        }

        $segments[] = $this->base64UrlEncode($signature);

        return implode('.', $segments);
    }

    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
