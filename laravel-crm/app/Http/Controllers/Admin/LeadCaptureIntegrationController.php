<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class LeadCaptureIntegrationController extends Controller
{
    /**
     * Display directory of all active lead capture URLs and webhooks using native CRM Blade layout.
     */
    public function index()
    {
        $baseUrl = config('app.url');

        $integrations = [
            [
                'name' => 'Facebook & Instagram Lead Ads',
                'icon' => '📘',
                'description' => 'Real-time webhook ingestion for Meta Lead Ads campaigns.',
                'endpoint' => "{$baseUrl}/api/v1/lead-capture/facebook",
                'status' => 'Active',
            ],
            [
                'name' => 'TikTok Lead Generation',
                'icon' => '🎵',
                'description' => 'Instant webhook endpoint for TikTok Lead Forms.',
                'endpoint' => "{$baseUrl}/api/v1/lead-capture/tiktok",
                'status' => 'Active',
            ],
            [
                'name' => 'LinkedIn Lead Gen Forms',
                'icon' => '💼',
                'description' => 'Direct B2B lead capture from LinkedIn sponsored forms.',
                'endpoint' => "{$baseUrl}/api/v1/lead-capture/linkedin",
                'status' => 'Active',
            ],
            [
                'name' => 'Inbound Call Tracking',
                'icon' => '📞',
                'description' => 'Turns missed & inbound calls into new leads.',
                'endpoint' => "{$baseUrl}/api/v1/lead-capture/call-tracking",
                'status' => 'Active',
            ],
            [
                'name' => 'WordPress & ClickFunnels Webforms',
                'icon' => '🌐',
                'description' => 'Generic HTML / Webform POST integration.',
                'endpoint' => "{$baseUrl}/api/v1/lead-capture/webform",
                'status' => 'Active',
            ],
            [
                'name' => 'Zapier & Make Bridges',
                'icon' => '⚡',
                'description' => 'Universal Zapier webhook bridge.',
                'endpoint' => "{$baseUrl}/api/v1/lead-capture/zapier",
                'status' => 'Active',
            ],
        ];

        return view('admin::lead_capture.integrations', compact('integrations'));
    }
}
