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
        $query = $this->leadRepository->getModel()->newQuery();

        $query->where(function ($q) use ($emails, $contactNumbers, $externalId, $origin) {
            // Check external ID if provided
            if ($externalId && $origin) {
                $q->orWhere(function ($subQ) use ($externalId, $origin) {
                    $subQ->where('external_id', $externalId)
                        ->where('origin', $origin);
                });
            }

            // Check Emails
            if (! empty($emails)) {
                $q->orWhere(function ($subQ) use ($emails) {
                    foreach ($emails as $email) {
                        $value = is_array($email) ? ($email['value'] ?? null) : $email;
                        if ($value) {
                            $subQ->orWhereJsonContains('emails', ['value' => $value]);
                        }
                    }
                });
            }

            // Check Phones
            if (! empty($contactNumbers)) {
                $q->orWhere(function ($subQ) use ($contactNumbers) {
                    foreach ($contactNumbers as $phone) {
                        $value = is_array($phone) ? ($phone['value'] ?? null) : $phone;
                        if ($value) {
                            $subQ->orWhereJsonContains('contact_numbers', ['value' => $value]);
                        }
                    }
                });
            }
        });

        return $query->first();
    }
}
