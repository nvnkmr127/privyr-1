<?php

namespace Webkul\Lead\Services;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Webkul\Lead\Contracts\Lead;
use Webkul\Lead\Models\LeadCaptureLog;
use Webkul\Lead\Models\LeadSourceConnector;
use Webkul\Lead\Repositories\LeadRepository;
use Webkul\Lead\Repositories\PipelineRepository;

class LeadCaptureService
{
    public function __construct(
        protected LeadRepository $leadRepository,
        protected PipelineRepository $pipelineRepository
    ) {}

    /**
     * Process incoming payload from any connected source (Meta, Google, IndiaMART, JustDial, Webhook, API, Zapier, QR).
     *
     * @return Lead|null
     */
    public function processIncomingPayload(LeadSourceConnector $connector, array $payload, ?string $rawBody = null, ?string $signature = null, bool $dryRun = false)
    {
        try {
            // Meta connectors: verify the payload signature, then resolve the full
            // lead from Graph API when only a leadgen_id was pushed.
            if ($connector->source_type === 'meta_ads') {
                $this->assertValidFacebookSignature($rawBody, $signature);
                $payload = $this->maybeFetchFacebookLead($connector, $payload);
            }

            // Flatten structured ad-platform payloads (Meta field_data, Google
            // user_column_data) into scalar keys the field mapper can read.
            $payload = $this->normalizeStructuredPayload($payload);

            $mappedData = $this->mapPayloadToFields($payload, $connector->field_mappings ?? []);

            $email = $mappedData['person']['emails'] ?? null;
            $phone = $mappedData['person']['contact_numbers'] ?? null;

            // Duplicate Detection
            $existingLead = $this->detectDuplicateContact($email, $phone);

            // Dry run: exercise the real mapping / dedup / routing path and report
            // what WOULD happen, without persisting a Lead, or log. Used by
            // the admin "Test integration" action so tests never create prod data.
            if ($dryRun) {
                $pipelineId = $connector->default_lead_pipeline_id ?? $this->pipelineRepository->getDefaultPipeline()?->id;

                return [
                    'dry_run' => true,
                    'name' => $mappedData['person']['name'] ?? null,
                    'email' => $email,
                    'phone' => $phone,
                    'title' => $mappedData['title'] ?? ($connector->name.' - '.($mappedData['person']['name'] ?? 'New Lead')),
                    'pipeline' => $this->pipelineRepository->find($pipelineId)?->name,
                    'duplicate' => (bool) $existingLead,
                    'duplicate_action' => $connector->duplicate_action,
                    'would_create_lead' => ! ($existingLead && $connector->duplicate_action === 'skip'),
                ];
            }

            if ($existingLead && $connector->duplicate_action === 'skip') {
                LeadCaptureLog::create([
                    'connector_id' => $connector->id,
                    'raw_payload' => $payload,
                    'status' => 'duplicate_flagged',
                    'error_message' => 'Duplicate contact found. Ingestion skipped as per connector configuration.',
                ]);

                return null;
            }

            // Pipeline & Stage Fallbacks
            $pipelineId = $connector->default_lead_pipeline_id ?? $this->pipelineRepository->getDefaultPipeline()?->id;
            $pipeline = $this->pipelineRepository->find($pipelineId);
            $stageId = $connector->default_lead_pipeline_stage_id ?? $pipeline?->stages?->first()?->id;

            // Create or update Lead
            $leadTitle = $mappedData['title'] ?? ($connector->name.' - '.($mappedData['person']['name'] ?? 'New Lead'));
            $customLeadAttributes = Arr::except($mappedData, ['person', 'title', 'description', 'lead_value', 'utm_source', 'utm_medium', 'utm_campaign', 'location']);

            $leadData = array_merge([
                'entity_type' => 'leads',
                'title' => $leadTitle,
                'description' => $mappedData['description'] ?? 'Captured automatically via '.$connector->name,
                'lead_value' => $mappedData['lead_value'] ?? 0,
                'person_name' => $mappedData['person']['name'] ?? 'Web Lead Contact',
                'emails' => $email ? [['value' => $email, 'label' => 'work']] : [],
                'contact_numbers' => $phone ? [['value' => $phone, 'label' => 'mobile']] : [],
                'user_id' => $connector->default_user_id,
                'lead_source_id' => $connector->lead_source_id ?? null,
                'lead_pipeline_id' => $pipelineId,
                'lead_pipeline_stage_id' => $stageId,
                'is_unread' => true,
                'last_contacted_at' => null,
                'utm_source' => $mappedData['utm_source'] ?? null,
                'utm_medium' => $mappedData['utm_medium'] ?? null,
                'utm_campaign' => $mappedData['utm_campaign'] ?? null,
                'location' => $mappedData['location'] ?? null,
            ], $customLeadAttributes);

            if ($existingLead && in_array($connector->duplicate_action, ['update', 'attach_contact'])) {
                if (isset($leadData['person'])) {
                    throw new \Exception('PERSON IS IN LEADDATA UPDATE');
                }
                $lead = $this->leadRepository->update($leadData, $existingLead->id);
            } else {
                if (isset($leadData['person'])) {
                    throw new \Exception('PERSON IS IN LEADDATA CREATE');
                }
                $lead = $this->leadRepository->create($leadData);
            }
            if (array_key_exists('person', $lead->getAttributes())) {
                throw new \Exception('PERSON IS IN LEAD ATTRIBUTES IMMEDIATELY AFTER CREATE/UPDATE');
            }

            // Update Connector Statistics
            $connector->increment('captured_count');
            $connector->update(['last_received_at' => Carbon::now()]);

            // Create Log Entry
            LeadCaptureLog::create([
                'connector_id' => $connector->id,
                'raw_payload' => $payload,
                'status' => 'success',
                'lead_id' => $lead->id,
            ]);

            return $lead;
        } catch (\Throwable $e) {
            if (! $dryRun) {
                LeadCaptureLog::create([
                    'connector_id' => $connector->id,
                    'raw_payload' => $payload,
                    'status' => 'error',
                    'error_message' => $e->getMessage(),
                ]);
            }

            throw $e;
        }
    }

    /**
     * Map payload keys to CRM attributes using configured mappings or smart heuristics.
     */
    public function mapPayloadToFields(array $payload, array $fieldMappings = []): array
    {
        $result = [
            'title' => null,
            'description' => null,
            'lead_value' => null,
            'person' => [
                'name' => null,
                'emails' => null,
                'contact_numbers' => null,
            ],
        ];

        // 1. If explicit field mappings exist, apply them
        if (! empty($fieldMappings)) {
            foreach ($fieldMappings as $incomingKey => $crmTarget) {
                $value = data_get($payload, $incomingKey);
                if ($value !== null) {
                    data_set($result, $crmTarget, $value);
                }
            }
        }

        // 2. Smart Heuristic Auto-Mapper for unmapped keys
        $flatPayload = Arr::dot($payload);
        foreach ($flatPayload as $key => $val) {
            if (empty($val) || is_array($val)) {
                continue;
            }

            $lowerKey = Str::lower($key);

            if (! $result['person']['name'] && (Str::contains($lowerKey, ['name', 'full_name', 'sender_name', 'contact_name']))) {
                $result['person']['name'] = $val;
            } elseif (! $result['person']['emails'] && (Str::contains($lowerKey, ['email', 'mail']))) {
                $result['person']['emails'] = $val;
            } elseif (! $result['person']['contact_numbers'] && (Str::contains($lowerKey, ['phone', 'mobile', 'contact', 'whatsapp', 'tel']))) {
                $result['person']['contact_numbers'] = $val;
            } elseif (! $result['title'] && (Str::contains($lowerKey, ['title', 'subject', 'query', 'requirement', 'product']))) {
                $result['title'] = $val;
            } elseif (! $result['lead_value'] && (Str::contains($lowerKey, ['budget', 'value', 'price', 'amount'])) && is_numeric($val)) {
                $result['lead_value'] = (float) $val;
            } elseif (! $result['description'] && (Str::contains($lowerKey, ['description', 'notes', 'comment', 'message']))) {
                $result['description'] = $val;
            } elseif (! isset($result['utm_source']) && (Str::contains($lowerKey, ['utm_source', 'source']))) {
                $result['utm_source'] = $val;
            } elseif (! isset($result['utm_medium']) && (Str::contains($lowerKey, ['utm_medium', 'medium']))) {
                $result['utm_medium'] = $val;
            } elseif (! isset($result['utm_campaign']) && (Str::contains($lowerKey, ['utm_campaign', 'campaign']))) {
                $result['utm_campaign'] = $val;
            } elseif (! isset($result['location']) && (Str::contains($lowerKey, ['location', 'city', 'state', 'country', 'address']))) {
                $result['location'] = $val;
            }
        }

        return $result;
    }

    /**
     * Reject the payload if a Facebook app secret is configured and the
     * X-Hub-Signature-256 header does not match. No-op when either is absent.
     */
    protected function assertValidFacebookSignature(?string $rawBody, ?string $signature): void
    {
        $secret = config('services.facebook.client_secret');

        if (! $secret || ! $signature || $rawBody === null) {
            return;
        }

        $expected = 'sha256='.hash_hmac('sha256', $rawBody, $secret);

        if (! hash_equals($expected, $signature)) {
            throw new \RuntimeException('Invalid Facebook webhook signature.');
        }
    }

    /**
     * Meta pushes only a leadgen_id; fetch the full lead from the Graph API using
     * the connector's OAuth page token (falling back to the configured token).
     */
    protected function maybeFetchFacebookLead(LeadSourceConnector $connector, array $payload): array
    {
        $leadgenId = data_get($payload, 'entry.0.changes.0.value.leadgen_id') ?? ($payload['leadgen_id'] ?? null);

        if (! $leadgenId || ! empty($payload['field_data'])) {
            return $payload;
        }

        $token = $connector->meta_page_access_token ?: config('services.facebook.access_token');

        if (! $token) {
            return $payload;
        }

        $response = Http::get("https://graph.facebook.com/v18.0/{$leadgenId}", ['access_token' => $token]);

        return $response->successful() ? array_merge($payload, $response->json()) : $payload;
    }

    /**
     * Flatten structured ad-platform lead payloads into scalar keys so the
     * heuristic/explicit mapper can read them like any other webhook field.
     */
    protected function normalizeStructuredPayload(array $payload): array
    {
        // Meta Lead Ads: field_data => [['name' => 'email', 'values' => ['x']], ...]
        foreach ((array) ($payload['field_data'] ?? []) as $field) {
            $name = $field['name'] ?? null;
            if ($name !== null && ! isset($payload[$name])) {
                $payload[$name] = $field['values'][0] ?? null;
            }
        }

        // Google Lead Form Ads: user_column_data => [['column_name' => 'EMAIL', 'string_value' => 'x'], ...]
        foreach ((array) ($payload['user_column_data'] ?? []) as $column) {
            $name = $column['column_name'] ?? null;
            if ($name !== null && ! isset($payload[$name])) {
                $payload[$name] = $column['string_value'] ?? null;
            }
        }

        return $payload;
    }

    /**
     * Detect duplicate lead by email or phone number.
     *
     * @return Lead|null
     */
    public function detectDuplicateContact(?string $email, ?string $phone)
    {
        if (empty($email) && empty($phone)) {
            return null;
        }

        $query = $this->leadRepository->getModel()->newQuery();

        $query->where(function ($q) use ($email, $phone) {
            if ($email) {
                $q->where('emails', 'like', "%{$email}%");
            }

            if ($phone) {
                $q->orWhere('contact_numbers', 'like', "%{$phone}%");
            }
        });

        return $query->first();
    }
}
