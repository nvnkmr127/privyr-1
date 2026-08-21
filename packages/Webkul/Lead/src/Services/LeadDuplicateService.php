<?php

namespace Webkul\Lead\Services;

use Webkul\Lead\Repositories\LeadRepository;

class LeadDuplicateService
{
    public function __construct(
        protected LeadRepository $leadRepository
    ) {}

    /**
     * Normalize a phone number for duplicate checking.
     * Keeps leading + and digits, removes spaces, brackets, hyphens.
     */
    public function normalizePhone(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        $isPlus = str_starts_with(trim($phone), '+');
        $normalized = preg_replace('/[^0-9]/', '', $phone);

        if (empty($normalized)) {
            return null;
        }

        return $isPlus ? '+'.$normalized : $normalized;
    }

    /**
     * Normalize an email address for duplicate checking.
     */
    public function normalizeEmail(?string $email): ?string
    {
        if (empty($email)) {
            return null;
        }

        $data = ['emails' => [['value' => $email]]];
        $normalized = app(LeadDataQualityService::class)->normalize($data);

        return $normalized['normalized_primary_email'] ?? null;
    }

    /**
     * Detect duplicates based on provided lead data.
     */
    public function detect(array $leadData, string $origin, ?string $externalId = null): ?array
    {
        // Extract fields
        $email = $this->normalizeEmail($leadData['person']['emails'] ?? $leadData['emails'][0]['value'] ?? null);
        $phone = $this->normalizePhone($leadData['person']['contact_numbers'] ?? $leadData['contact_numbers'][0]['value'] ?? null);
        $name = trim($leadData['person_name'] ?? $leadData['person']['name'] ?? '');

        // 1. Exact Source + External ID Match (High Confidence)
        if ($origin && $externalId) {
            $existing = $this->leadRepository->getModel()
                ->where('origin', $origin)
                ->where('external_id', $externalId)
                ->where('is_merged', false)
                ->first();

            if ($existing) {
                return $this->buildResult($existing, 'external_id', 'high');
            }
        }

        $query = $this->leadRepository->getModel()->newQuery()->where('is_merged', false);

        // 2. Exact Phone Match (High Confidence)
        if ($phone) {
            $existing = (clone $query)->where('normalized_primary_phone', $phone)->first();
            if ($existing) {
                return $this->buildResult($existing, 'exact_phone', 'high');
            }
        }

        // 3. Exact Email Match (High Confidence)
        if ($email) {
            $existing = (clone $query)->where('normalized_primary_email', $email)->first();
            if ($existing) {
                return $this->buildResult($existing, 'exact_email', 'high');
            }
        }

        // 4. Medium Confidence Matches (Phone/Email + Name)
        // Since we didn't find exact matches on primary, we might check JSON fields just in case,
        // or check if similar name exists with the same phone/email (but they didn't match exactly above).
        // If the normalized primary didn't match, we can check the JSON arrays `emails` or `contact_numbers`
        // just in case they have it as a secondary contact.

        $mediumQuery = clone $query;
        $hasMediumQuery = false;

        $mediumQuery->where(function ($q) use ($email, $phone, $name, &$hasMediumQuery) {
            if ($email && $name) {
                $q->orWhere(function ($sub) use ($email, $name) {
                    $sub->where('emails', 'LIKE', '%'.$email.'%')
                        ->where('person_name', 'LIKE', '%'.$name.'%');
                });
                $hasMediumQuery = true;
            }
            if ($phone && $name) {
                $q->orWhere(function ($sub) use ($phone, $name) {
                    $sub->where('contact_numbers', 'LIKE', '%'.$phone.'%')
                        ->where('person_name', 'LIKE', '%'.$name.'%');
                });
                $hasMediumQuery = true;
            }
        });

        if ($hasMediumQuery) {
            $existing = $mediumQuery->first();
            if ($existing) {
                // Determine which matched
                if ($email && str_contains(json_encode($existing->emails), $email)) {
                    return $this->buildResult($existing, 'fuzzy_email_name', 'medium');
                }
                if ($phone && str_contains(json_encode($existing->contact_numbers), $phone)) {
                    return $this->buildResult($existing, 'fuzzy_phone_name', 'medium');
                }
            }
        }

        return null;
    }

    /**
     * Build the standard result payload.
     */
    protected function buildResult($lead, string $matchType, string $confidence): array
    {
        return [
            'existing_lead_id' => $lead->id,
            'match_type' => $matchType,
            'confidence' => $confidence,
            'existing_lead_data' => [
                'title' => $lead->title,
                'person_name' => $lead->person_name,
                'owner' => $lead->user ? $lead->user->name : 'Unassigned',
                'status' => $lead->status,
                'stage' => $lead->stage ? $lead->stage->name : null,
                'last_activity' => $lead->last_contacted_at,
                'source' => $lead->source ? $lead->source->name : null,
            ],
        ];
    }
}
