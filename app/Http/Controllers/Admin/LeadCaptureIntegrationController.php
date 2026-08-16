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
            [
                'name' => 'Google Ads Lead Forms',
                'icon' => 'G',
                'description' => 'Direct integration for Google Search and YouTube Lead Forms.',
                'endpoint' => "{$baseUrl}/api/v1/lead-capture/google",
                'status' => 'Active',
            ],
            [
                'name' => 'IndiaMART',
                'icon' => '🇮🇳',
                'description' => 'Capture leads from IndiaMART seller portal.',
                'endpoint' => "{$baseUrl}/api/v1/lead-capture/indiamart",
                'status' => 'Active',
            ],
            [
                'name' => 'JustDial',
                'icon' => '📞',
                'description' => 'Instant alerts for JustDial enquiries.',
                'endpoint' => "{$baseUrl}/api/v1/lead-capture/justdial",
                'status' => 'Active',
            ],
            [
                'name' => '99acres',
                'icon' => '🏢',
                'description' => 'Real estate leads from 99acres.',
                'endpoint' => "{$baseUrl}/api/v1/lead-capture/99acres",
                'status' => 'Active',
            ],
            [
                'name' => 'MagicBricks',
                'icon' => '🏠',
                'description' => 'Real estate leads from MagicBricks.',
                'endpoint' => "{$baseUrl}/api/v1/lead-capture/magicbricks",
                'status' => 'Active',
            ],
            [
                'name' => 'Housing.com',
                'icon' => '🏘️',
                'description' => 'Real estate leads from Housing.com.',
                'endpoint' => "{$baseUrl}/api/v1/lead-capture/housing",
                'status' => 'Active',
            ],
            [
                'name' => 'Sulekha',
                'icon' => '🛠️',
                'description' => 'Service enquiries from Sulekha.',
                'endpoint' => "{$baseUrl}/api/v1/lead-capture/sulekha",
                'status' => 'Active',
            ],
            [
                'name' => 'QR Code Scans',
                'icon' => '📱',
                'description' => 'Lead capture from printed QR codes.',
                'endpoint' => "{$baseUrl}/api/v1/lead-capture/qr",
                'status' => 'Active',
            ],
            [
                'name' => 'Custom API & Webhooks',
                'icon' => '🔌',
                'description' => 'Generic JSON payload endpoint.',
                'endpoint' => "{$baseUrl}/api/v1/lead-capture/webhook",
                'status' => 'Active',
            ],
        ];

        return view('admin::lead_capture.integrations', compact('integrations'));
    }
}
