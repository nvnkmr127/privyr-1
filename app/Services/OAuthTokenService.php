<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OAuthTokenService
{
    /**
     * Get Facebook Graph API Page Access Token.
     */
    public function getFacebookAccessToken(): ?string
    {
        return config('services.facebook.access_token');
    }

    /**
     * Fetch Facebook Lead details using Graph API.
     *
     * @param string $leadGenId
     * @return array|null
     */
    public function fetchFacebookLead(string $leadGenId): ?array
    {
        $token = $this->getFacebookAccessToken();

        if (!$token) {
            Log::warning("Facebook Graph API call skipped: FACEBOOK_PAGE_ACCESS_TOKEN is not configured.");
            return null;
        }

        $response = Http::get("https://graph.facebook.com/v18.0/{$leadGenId}", [
            'access_token' => $token,
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error("Facebook Graph API fetch error: " . $response->body());
        return null;
    }
}
