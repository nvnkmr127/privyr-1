<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Send a WhatsApp message via Watxio. Returns true on dispatch.
     * No-op (logs) when Watxio isn't configured, so the CRM works ungated.
     */
    public function send(string $phone, string $message): bool
    {
        $endpoint = config('services.watxio.endpoint');
        $token = config('services.watxio.token');

        if (! $endpoint || ! $token) {
            Log::info("Watxio not configured; WhatsApp send skipped for {$phone}.");

            return false;
        }

        $clean = preg_replace('/[^0-9]/', '', $phone);

        // ponytail: payload assumes Watxio accepts {to, message}; adjust keys to the
        // real Watxio API contract when available.
        $response = Http::withToken($token)->post($endpoint, [
            'to' => $clean,
            'message' => $message,
        ]);

        if (! $response->successful()) {
            Log::error('Watxio WhatsApp send failed: '.$response->body());

            return false;
        }

        return true;
    }
}
