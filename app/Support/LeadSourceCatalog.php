<?php

namespace App\Support;

/**
 * Single source of truth for the lead-capture sources shown on
 * /admin/lead-capture/integrations.
 *
 * Every source is grouped by an integration `kind` that reflects how leads
 * actually arrive — this drives a genuinely source-appropriate flow rather than
 * a shared one:
 *
 *   - webhook : the external platform POSTs JSON to the connector's token URL.
 *   - embed   : we host a lead form; the customer embeds it (iframe / JS snippet).
 *   - oauth   : like webhook, but first needs an OAuth page connection (Meta).
 *
 * Per-source `instructions` are the concrete setup steps for that platform.
 */
class LeadSourceCatalog
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function all(): array
    {
        return [
            [
                'source_type' => 'meta_ads',
                'name' => 'Facebook & Instagram Lead Ads',
                'icon' => '<i class="fa-brands fa-facebook-f"></i>',
                'kind' => 'oauth',
                'description' => 'Auto-pull leads from Meta Lead Ads the moment a form is submitted.',
                'instructions' => 'Connect your Facebook Page below. We subscribe it to the leadgen webhook and set the Callback URL + Verify Token automatically. Instagram lead forms on the same Page are included.',
            ],
            [
                'source_type' => 'google_ads',
                'name' => 'Google Ads Lead Forms',
                'icon' => '<i class="fa-brands fa-google"></i>',
                'kind' => 'webhook',
                'description' => 'Google Search & YouTube Lead Form extensions.',
                'instructions' => 'In Google Ads → your Lead Form asset → Delivery options, set the Webhook URL to the URL below and the Key to any value. Leads POST here as they come in.',
            ],
            [
                'source_type' => 'tiktok',
                'name' => 'TikTok Lead Generation',
                'icon' => '<i class="fa-brands fa-tiktok"></i>',
                'kind' => 'webhook',
                'description' => 'TikTok Lead Forms.',
                'instructions' => 'In TikTok Ads Manager → Instant Forms → Integrations, add a webhook pointing at the URL below.',
            ],
            [
                'source_type' => 'linkedin',
                'name' => 'LinkedIn Lead Gen Forms',
                'icon' => '<i class="fa-brands fa-linkedin-in"></i>',
                'kind' => 'webhook',
                'description' => 'B2B leads from LinkedIn sponsored forms.',
                'instructions' => 'Use a connector (Zapier / Make / LinkedIn CRM sync) to forward Lead Gen Form submissions as JSON to the URL below.',
            ],
            [
                'source_type' => 'call_tracking',
                'name' => 'Inbound Call Tracking',
                'icon' => '<i class="fa-solid fa-phone"></i>',
                'kind' => 'webhook',
                'description' => 'Turn missed & inbound calls into leads.',
                'instructions' => 'Point your call-tracking provider\'s webhook (call completed / missed call event) at the URL below. Map the caller number to Phone.',
            ],
            [
                'source_type' => 'webform',
                'name' => 'WordPress & ClickFunnels Webforms',
                'icon' => '<i class="fa-brands fa-wordpress-simple"></i>',
                'kind' => 'embed',
                'description' => 'Embed a hosted capture form on any website.',
                'instructions' => 'Paste the embed snippet below into your WordPress page, ClickFunnels step, or any HTML site. Submissions are captured as leads instantly.',
            ],
            [
                'source_type' => 'webhook',
                'name' => 'Zapier & Make Bridges',
                'icon' => '<i class="fa-solid fa-bolt"></i>',
                'kind' => 'webhook',
                'description' => 'Universal Zapier / Make webhook bridge.',
                'instructions' => 'In Zapier or Make, add a "Webhook → POST" action to the URL below and send your lead fields as JSON.',
            ],
            [
                'source_type' => 'indiamart',
                'name' => 'IndiaMART',
                'icon' => '<i class="fa-solid fa-store"></i>',
                'kind' => 'webhook',
                'description' => 'Leads from the IndiaMART seller portal.',
                'instructions' => 'In IndiaMART Seller Panel → Lead Manager → Push API / CRM Integration, set the Push URL to the URL below.',
            ],
            [
                'source_type' => 'justdial',
                'name' => 'JustDial',
                'icon' => '<i class="fa-solid fa-address-book"></i>',
                'kind' => 'webhook',
                'description' => 'Enquiries from JustDial.',
                'instructions' => 'Share the URL below with JustDial support / your KAM to configure real-time lead push (JD API).',
            ],
            [
                'source_type' => 'realestate_99acres',
                'name' => '99acres',
                'icon' => '<i class="fa-solid fa-building"></i>',
                'kind' => 'webhook',
                'description' => 'Real estate leads from 99acres.',
                'instructions' => 'In your 99acres seller dashboard, configure lead push / CRM integration to POST to the URL below.',
            ],
            [
                'source_type' => 'magicbricks',
                'name' => 'MagicBricks',
                'icon' => '<i class="fa-solid fa-house"></i>',
                'kind' => 'webhook',
                'description' => 'Real estate leads from MagicBricks.',
                'instructions' => 'Configure MagicBricks lead push / CRM integration to POST to the URL below.',
            ],
            [
                'source_type' => 'housing',
                'name' => 'Housing.com',
                'icon' => '<i class="fa-solid fa-city"></i>',
                'kind' => 'webhook',
                'description' => 'Real estate leads from Housing.com.',
                'instructions' => 'Configure Housing.com lead push / CRM integration to POST to the URL below.',
            ],
            [
                'source_type' => 'sulekha',
                'name' => 'Sulekha',
                'icon' => '<i class="fa-solid fa-wrench"></i>',
                'kind' => 'webhook',
                'description' => 'Service enquiries from Sulekha.',
                'instructions' => 'Configure Sulekha business lead delivery to POST to the URL below.',
            ],
            [
                'source_type' => 'qr',
                'name' => 'QR Code / Hosted Form',
                'icon' => '<i class="fa-solid fa-qrcode"></i>',
                'kind' => 'embed',
                'description' => 'A shareable hosted form + printable QR code.',
                'instructions' => 'Share the hosted form link, or print its QR code for events and storefronts. You can also embed it on a page using the snippet below.',
            ],
            [
                'source_type' => 'rest_api',
                'name' => 'Custom API & Webhooks',
                'icon' => '<i class="fa-solid fa-network-wired"></i>',
                'kind' => 'webhook',
                'description' => 'Generic JSON payload endpoint for your own code.',
                'instructions' => 'POST a JSON body with your lead fields to the URL below. See the sample request. Use Field Mapping if your keys differ from name / email / phone.',
            ],
        ];
    }

    public static function find(string $sourceType): ?array
    {
        foreach (self::all() as $source) {
            if ($source['source_type'] === $sourceType) {
                return $source;
            }
        }

        return null;
    }

    /**
     * The set of source_types we recognise — used to validate incoming requests
     * so the browser cannot invent an arbitrary source.
     *
     * @return array<int, string>
     */
    public static function sourceTypes(): array
    {
        return array_column(self::all(), 'source_type');
    }
}
