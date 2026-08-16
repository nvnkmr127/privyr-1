<?php

namespace App\Services;

use App\Models\DeviceToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    /**
     * Send a push notification to a set of device tokens via Firebase Cloud
     * Messaging (or a compatible endpoint).
     *
     * Mirrors WhatsAppService: it is a safe no-op (logs only) when push isn't
     * configured, so the CRM works ungated out of the box.
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

        $key = config('services.push.key');

        if (! $key) {
            Log::info('Push not configured; new-lead push skipped for '.count($tokens).' device(s).');

            return ['sent' => false, 'invalid_tokens' => [], 'reason' => 'not_configured'];
        }

        $response = Http::withHeaders([
            'Authorization' => 'key='.$key,
            'Content-Type' => 'application/json',
        ])->post(config('services.push.endpoint'), [
            'registration_ids' => $tokens,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'data' => $data,
            'priority' => 'high',
        ]);

        if (! $response->successful()) {
            Log::error('FCM push failed: '.$response->body());

            return ['sent' => false, 'invalid_tokens' => [], 'reason' => 'http_error'];
        }

        // FCM reports per-token results positionally; surface dead tokens so the
        // caller can prune them, and count real deliveries so an HTTP 200 with
        // zero successes isn't mistaken for a delivered push.
        $results = (array) $response->json('results', []);
        $invalid = [];
        $successCount = 0;

        foreach ($results as $index => $result) {
            $error = $result['error'] ?? null;

            if ($error === null && ! empty($result['message_id'])) {
                $successCount++;
            }

            if (in_array($error, ['NotRegistered', 'InvalidRegistration', 'MismatchSenderId'], true)
                && isset($tokens[$index])
            ) {
                $invalid[] = $tokens[$index];
            }
        }

        // Fall back to FCM's top-level success counter when per-result data is absent.
        if ($successCount === 0) {
            $successCount = (int) $response->json('success', 0);
        }

        if ($successCount === 0) {
            Log::warning('FCM push accepted but delivered to zero devices: '.$response->body());

            return ['sent' => false, 'invalid_tokens' => $invalid, 'reason' => 'no_delivery'];
        }

        return ['sent' => true, 'invalid_tokens' => $invalid, 'reason' => null];
    }

    /**
     * Push to every device registered to a given agent, pruning any tokens the
     * provider rejects and stamping the rest as freshly used.
     *
     * @param  \Webkul\User\Contracts\User|object|null  $user
     * @param  array<string, mixed>  $data
     */
    public function sendToUser($user, string $title, string $body, array $data = []): bool
    {
        if (! $user) {
            return false;
        }

        $tokens = DeviceToken::where('user_id', $user->id)->pluck('token')->all();

        if (empty($tokens)) {
            return false;
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

        return $result['sent'];
    }
}
