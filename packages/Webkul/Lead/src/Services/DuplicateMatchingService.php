<?php

namespace Webkul\Lead\Services;

use Webkul\Lead\Models\Lead;
use Webkul\Lead\Repositories\LeadRepository;

class DuplicateMatchingService
{
    public function __construct(
        protected LeadRepository $leadRepository
    ) {}

    /**
     * Check if a duplicate lead exists based on emails, contact_numbers, or external_id.
     *
     * @return \Webkul\Lead\Contracts\Lead|null
     */
    public function findDuplicate(array $emails, array $contactNumbers, ?string $externalId = null, ?string $origin = null)
    {
        $leadData = [
            'emails' => array_map(fn($e) => is_array($e) ? $e : ['value' => $e], $emails),
            'contact_numbers' => array_map(fn($p) => is_array($p) ? $p : ['value' => $p], $contactNumbers),
        ];

        $result = app(LeadDuplicateService::class)->detect($leadData, $origin ?? '', $externalId);

        if ($result && isset($result['existing_lead_id'])) {
            return $this->leadRepository->find($result['existing_lead_id']);
        }

        return null;
    }
}
