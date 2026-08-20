<?php

namespace Webkul\Lead\Services;

use Webkul\Lead\Models\LeadAttributionHistoryProxy;
use Webkul\Lead\Contracts\Lead;

class LeadAttributionService
{
    /**
     * Map ingestion payload data to first/latest touch attribution fields.
     */
    public function mapAttributionData(array $data, bool $isUpdate = false): array
    {
        $sourceId = $data['lead_source_id'] ?? null;
        $origin = $data['origin'] ?? null;
        $externalId = $data['external_id'] ?? null;
        $campaign = $data['campaign'] ?? null;
        $medium = $data['medium'] ?? null;
        $content = $data['content'] ?? null;
        $term = $data['term'] ?? null;
        $landingPage = $data['landing_page'] ?? null;
        $form = $data['form_id'] ?? $data['form'] ?? null;
        $externalSource = $data['external_source'] ?? null;

        if (! $isUpdate) {
            // First Touch (only set on creation)
            $data['first_lead_source_id'] = $sourceId;
            $data['first_origin'] = $origin;
            $data['first_external_id'] = $externalId;
            $data['first_campaign'] = $campaign;
            $data['first_medium'] = $medium;
            $data['first_content'] = $content;
            $data['first_term'] = $term;
            $data['first_landing_page'] = $landingPage;
            $data['first_form'] = $form;
            $data['first_external_source'] = $externalSource;
        }

        // Latest Touch (always updated)
        $data['latest_lead_source_id'] = $sourceId;
        $data['latest_origin'] = $origin;
        $data['latest_external_id'] = $externalId;
        $data['latest_campaign'] = $campaign;
        $data['latest_medium'] = $medium;
        $data['latest_content'] = $content;
        $data['latest_term'] = $term;
        $data['latest_landing_page'] = $landingPage;
        $data['latest_form'] = $form;
        $data['latest_external_source'] = $externalSource;

        return $data;
    }

    /**
     * Record a new attribution history entry.
     */
    public function recordHistory(Lead $lead, array $data): void
    {
        LeadAttributionHistoryProxy::create([
            'lead_id' => $lead->id,
            'lead_source_id' => $data['lead_source_id'] ?? null,
            'origin' => $data['origin'] ?? null,
            'campaign' => $data['campaign'] ?? null,
            'medium' => $data['medium'] ?? null,
            'content' => $data['content'] ?? null,
            'term' => $data['term'] ?? null,
            'landing_page' => $data['landing_page'] ?? null,
            'form' => $data['form_id'] ?? $data['form'] ?? null,
            'external_source' => $data['external_source'] ?? null,
            'external_id' => $data['external_id'] ?? null,
        ]);
        
        event('lead.attribution.updated', $lead);
    }
}
