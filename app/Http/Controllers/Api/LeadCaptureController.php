<?php

namespace App\Http\Controllers\Api;

use App\Events\LeadCreatedEvent;
use App\Http\Controllers\Controller;
use App\Services\LeadDistributionService;
use App\Services\OAuthTokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Webkul\Admin\Http\Controllers\Lead\PublicLeadCaptureController;
use Webkul\Contact\Repositories\PersonRepository;
use Webkul\Lead\Repositories\LeadRepository;
use Webkul\Lead\Repositories\PipelineRepository;
use Webkul\Lead\Repositories\SourceRepository;
use Webkul\Lead\Services\LeadCaptureService;

/**
 * Legacy global (env-configured) lead ingestion API.
 *
 * The canonical, UI-driven path is the connector system:
 * {@see PublicLeadCaptureController} +
 * {@see LeadCaptureService}, exposed per-connector at
 * /api/v1/lead-capture/webhook/{token}. Prefer creating a Lead Connector
 * (Settings → Lead Connectors) over this controller's provider endpoints.
 */
class LeadCaptureController extends Controller
{
    public function __construct(
        protected LeadRepository $leadRepository,
        protected PersonRepository $personRepository,
        protected SourceRepository $sourceRepository,
        protected PipelineRepository $pipelineRepository,
        protected LeadDistributionService $distributionService,
        protected OAuthTokenService $oauthTokenService
    ) {}

    /**
     * Facebook webhook verification.
     */
    public function verify(Request $request)
    {
        $verifyToken = config('services.facebook.webhook_verify_token');

        if ($request->get('hub_mode') === 'subscribe'
            && $verifyToken
            && hash_equals($verifyToken, (string) $request->get('hub_verify_token'))
        ) {
            return response($request->get('hub_challenge'), 200);
        }

        return response()->json(['status' => 'error', 'message' => 'Verification failed.'], 403);
    }

    /**
     * Ingest lead from webhook or external API.
     */
    public function capture(Request $request, $provider = null)
    {
        try {
            $data = $request->all();
            Log::info("Lead capture incoming payload [Provider: {$provider}]:", $data);

            $secret = config('services.lead_capture.secret');
            if ($secret && ! in_array(strtolower((string) $provider), ['facebook', 'google'], true)) {
                $provided = $request->header('X-Capture-Secret') ?? $request->query('key');
                if (! is_string($provided) || ! hash_equals($secret, $provided)) {
                    return response()->json(['status' => 'error', 'message' => 'Unauthorized secret.'], 401);
                }
            }

            $fbSecret = config('services.facebook.client_secret');
            $fbSignature = $request->header('X-Hub-Signature-256');
            if ($fbSecret && $fbSignature) {
                $expected = 'sha256='.hash_hmac('sha256', $request->getContent(), $fbSecret);
                if (! hash_equals($expected, $fbSignature)) {
                    return response()->json(['status' => 'error', 'message' => 'Invalid FB signature.'], 401);
                }
            }

            $leadgenId = data_get($data, 'entry.0.changes.0.value.leadgen_id') ?? ($data['leadgen_id'] ?? null);
            if ($leadgenId && empty($data['field_data'])) {
                $fbLead = $this->oauthTokenService->fetchFacebookLead($leadgenId);
                if ($fbLead) {
                    $data = $fbLead;
                    $provider = 'facebook';
                }
            }

            $extracted = $this->extractPayloadFields($data, $provider);

            if (empty($extracted['phone']) && empty($extracted['email'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Lead payload must contain at least a valid phone number or email address.',
                ], 422);
            }

            // 1. De-duplicate or Create Person Contact
            $person = null;
            if (! empty($extracted['phone'])) {
                $cleanPhone = preg_replace('/[^0-9]/', '', $extracted['phone']);
                $person = DB::table('persons')
                    ->whereRaw("REPLACE(REPLACE(REPLACE(contact_numbers, ' ', ''), '-', ''), '+', '') LIKE ?", ["%{$cleanPhone}%"])
                    ->first();
            }

            if (! $person && ! empty($extracted['email'])) {
                $person = DB::table('persons')
                    ->where('emails', 'like', "%{$extracted['email']}%")
                    ->first();
            }

            $assignedUserId = $this->distributionService->resolveAssignedUserId($extracted);

            if (! $person) {
                $personData = [
                    'entity_type' => 'persons',
                    'name' => $extracted['name'],
                    'emails' => ! empty($extracted['email']) ? [['value' => $extracted['email'], 'label' => 'work']] : [],
                    'contact_numbers' => ! empty($extracted['phone']) ? [['value' => $extracted['phone'], 'label' => 'mobile']] : [],
                    'user_id' => $assignedUserId,
                ];

                $person = $this->personRepository->create($personData);
            }

            // 2. Resolve or Create Lead Source
            $source = $this->sourceRepository->findOneByField('name', $extracted['source']);
            if (! $source) {
                $source = $this->sourceRepository->create(['name' => $extracted['source']]);
            }

            // 3. Resolve Default Pipeline & Stage
            $pipeline = $this->pipelineRepository->getDefaultPipeline();
            $stage = $pipeline->stages->first();

            // 4. Create Lead
            $leadData = [
                'entity_type' => 'leads',
                'title' => $extracted['title'].($extracted['name'] ? " - {$extracted['name']}" : ''),
                'description' => $extracted['description'],
                'lead_value' => $extracted['value'],
                'user_id' => $assignedUserId,
                'person_id' => $person->id,
                'lead_source_id' => $source->id,
                'lead_pipeline_id' => $pipeline->id,
                'lead_pipeline_stage_id' => $stage->id,
                'status' => 1,
            ];

            $lead = $this->leadRepository->create($leadData);

            event(new LeadCreatedEvent([
                'id' => $lead->id,
                'title' => $lead->title,
                'source' => $source->name,
                'assigned_user_id' => $assignedUserId,
            ]));

            return response()->json([
                'status' => 'success',
                'message' => 'Lead captured and assigned successfully.',
                'data' => [
                    'lead_id' => $lead->id,
                    'person_id' => $person->id,
                    'assigned_user_id' => $assignedUserId,
                    'source' => $source->name,
                ],
            ], 201);
        } catch (\Throwable $e) {
            Log::error('Lead capture exception: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to capture lead: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Import WhatsApp Chat export into Lead.
     */
    public function importWhatsAppChat(Request $request)
    {
        $request->validate([
            'chat_text' => 'required|string',
            'contact_name' => 'nullable|string',
            'phone' => 'nullable|string',
        ]);

        $chatText = $request->input('chat_text');

        // Extract phone from chat text if not provided
        $phone = $request->input('phone');
        if (! $phone) {
            preg_match('/(\+?[0-9]{10,14})/', $chatText, $matches);
            $phone = $matches[1] ?? '9999999999';
        }

        $name = $request->input('contact_name') ?? 'WhatsApp Chat Lead';

        return $this->capture(new Request([
            'title' => 'WhatsApp Chat Inquiry',
            'name' => $name,
            'phone' => $phone,
            'source' => 'WhatsApp Import',
            'description' => "Imported Chat History:\n".substr($chatText, 0, 1000),
        ]), 'whatsapp');
    }

    /**
     * Normalize multi-source webhook payloads.
     */
    protected function extractPayloadFields(array $data, $provider = null): array
    {
        $provider = strtolower($provider ?? $data['provider'] ?? '');

        // TikTok Lead Gen
        if ($provider === 'tiktok' || isset($data['lead_info'])) {
            $info = $data['lead_info'] ?? $data;

            return [
                'title' => 'TikTok Ad Lead',
                'name' => $info['user_name'] ?? $info['name'] ?? 'TikTok Prospect',
                'phone' => $info['user_phone'] ?? $info['phone'] ?? null,
                'email' => $info['user_email'] ?? $info['email'] ?? null,
                'source' => 'TikTok Ads',
                'value' => 0,
                'description' => 'TikTok Ad Campaign #'.($data['ad_id'] ?? ''),
            ];
        }

        // LinkedIn Lead Gen Forms
        if ($provider === 'linkedin' || isset($data['formResponse'])) {
            $resp = $data['formResponse'] ?? $data;

            return [
                'title' => $resp['headline'] ?? 'LinkedIn Lead Form',
                'name' => trim(($resp['firstName'] ?? '').' '.($resp['lastName'] ?? '')) ?: 'LinkedIn Prospect',
                'phone' => $resp['phoneNumber'] ?? null,
                'email' => $resp['emailAddress'] ?? null,
                'source' => 'LinkedIn Ads',
                'value' => 0,
                'description' => 'LinkedIn Lead Gen Form',
            ];
        }

        // Call Tracking (Inbound Calls)
        if ($provider === 'call_tracking' || isset($data['caller_number'])) {
            return [
                'title' => 'Inbound Phone Call Inquiry',
                'name' => $data['caller_name'] ?? 'Caller '.($data['caller_number'] ?? ''),
                'phone' => $data['caller_number'] ?? null,
                'email' => null,
                'source' => 'Inbound Call',
                'value' => 0,
                'description' => 'Duration: '.($data['call_duration'] ?? '0').'s. Recording: '.($data['recording_url'] ?? 'N/A'),
            ];
        }

        // WordPress / ClickFunnels / Webforms
        if (in_array($provider, ['wordpress', 'clickfunnels', 'webform'])) {
            return [
                'title' => $data['form_name'] ?? $data['subject'] ?? 'Website Form Submission',
                'name' => $data['your-name'] ?? $data['name'] ?? $data['first_name'] ?? 'Web Prospect',
                'phone' => $data['your-phone'] ?? $data['phone'] ?? $data['mobile'] ?? null,
                'email' => $data['your-email'] ?? $data['email'] ?? null,
                'source' => ucfirst($provider).' Form',
                'value' => $data['value'] ?? 0,
                'description' => $data['your-message'] ?? $data['message'] ?? $data['notes'] ?? '',
            ];
        }

        // 99acres
        if ($provider === '99acres' || isset($data['QueryTitle']) || isset($data['SenderMobile'])) {
            return [
                'title' => $data['QueryTitle'] ?? $data['PropertyType'] ?? '99acres Property Lead',
                'name' => $data['SenderName'] ?? '99acres Prospect',
                'phone' => $data['SenderMobile'] ?? null,
                'email' => $data['SenderEmail'] ?? null,
                'source' => '99acres',
                'value' => $data['Budget'] ?? 0,
                'description' => $data['Locality'] ?? $data['QueryDetail'] ?? '',
            ];
        }

        // MagicBricks
        if ($provider === 'magicbricks' || isset($data['project_name'])) {
            return [
                'title' => $data['project_name'] ?? 'MagicBricks Lead',
                'name' => $data['name'] ?? 'MagicBricks Prospect',
                'phone' => $data['mobile'] ?? $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'source' => 'MagicBricks',
                'value' => $data['price'] ?? 0,
                'description' => $data['city'] ?? $data['user_type'] ?? '',
            ];
        }

        // Facebook Lead Ads
        if ($provider === 'facebook' || isset($data['field_data'])) {
            $fields = collect($data['field_data'] ?? [])->mapWithKeys(function ($field) {
                return [$field['name'] => $field['values'][0] ?? null];
            });

            return [
                'title' => $data['form_name'] ?? 'FB Lead Ad Inquiry',
                'name' => $fields['full_name'] ?? trim(($fields['first_name'] ?? '').' '.($fields['last_name'] ?? '')) ?: 'FB Prospect',
                'phone' => $fields['phone_number'] ?? $fields['phone'] ?? null,
                'email' => $fields['email'] ?? null,
                'source' => 'Facebook Ads',
                'value' => 0,
                'description' => 'Captured via FB Lead Ads form #'.($data['form_id'] ?? ''),
            ];
        }

        // Google Lead Forms
        if ($provider === 'google' || isset($data['user_column_data'])) {
            $fields = collect($data['user_column_data'] ?? [])->mapWithKeys(function ($col) {
                return [$col['column_name'] => $col['string_value'] ?? null];
            });

            return [
                'title' => 'Google Search Ad Lead',
                'name' => $fields['FULL_NAME'] ?? 'Google Prospect',
                'phone' => $fields['PHONE_NUMBER'] ?? null,
                'email' => $fields['EMAIL'] ?? null,
                'source' => 'Google Ads',
                'value' => 0,
                'description' => 'Google Ad Campaign: '.($data['campaign_id'] ?? ''),
            ];
        }

        // JustDial
        if ($provider === 'justdial' || isset($data['lead_id'], $data['category'])) {
            return [
                'title' => $data['category'] ?? 'JustDial Enquiry',
                'name' => $data['name'] ?? 'JustDial Prospect',
                'phone' => $data['mobile'] ?? $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'source' => 'JustDial',
                'value' => 0,
                'description' => $data['area'] ?? $data['city'] ?? '',
            ];
        }

        // Housing.com
        if ($provider === 'housing' || isset($data['project_id'], $data['lead_type'])) {
            return [
                'title' => $data['project_name'] ?? 'Housing.com Lead',
                'name' => $data['user_name'] ?? $data['name'] ?? 'Housing Prospect',
                'phone' => $data['user_phone'] ?? $data['phone'] ?? null,
                'email' => $data['user_email'] ?? $data['email'] ?? null,
                'source' => 'Housing.com',
                'value' => 0,
                'description' => $data['message'] ?? '',
            ];
        }

        // Sulekha
        if ($provider === 'sulekha' || isset($data['Service'], $data['City'])) {
            return [
                'title' => $data['Service'] ?? 'Sulekha Enquiry',
                'name' => $data['Name'] ?? $data['name'] ?? 'Sulekha Prospect',
                'phone' => $data['Mobile'] ?? $data['phone'] ?? null,
                'email' => $data['Email'] ?? null,
                'source' => 'Sulekha',
                'value' => 0,
                'description' => $data['City'] ?? $data['Need'] ?? '',
            ];
        }

        // Default Generic Webhook / Zapier / Telegram / IndiaMART / JustDial
        return [
            'title' => $data['title'] ?? $data['subject'] ?? $data['SUBJECT'] ?? $data['product_name'] ?? 'New Webhook Lead',
            'name' => $data['name'] ?? $data['sender_name'] ?? $data['SENDER_NAME'] ?? $data['full_name'] ?? 'Unknown Prospect',
            'phone' => $data['phone'] ?? $data['mobile'] ?? $data['SENDER_MOBILE'] ?? $data['SENDER_PHONE'] ?? null,
            'email' => $data['email'] ?? $data['SENDER_EMAIL'] ?? null,
            'source' => $data['source'] ?? $data['source_name'] ?? ($data['SENDER_MOBILE'] ? 'IndiaMART' : ($provider ?: 'Webhook')),
            'value' => $data['value'] ?? $data['lead_value'] ?? 0,
            'description' => $data['description'] ?? $data['query'] ?? $data['QUERY_PRODUCT_NAME'] ?? '',
        ];
    }
}
