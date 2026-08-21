<?php

namespace Webkul\Lead\Services;

use Illuminate\Support\Facades\Event;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Models\LeadQualificationProxy;

class LeadQualificationService
{
    /**
     * @var LeadScoringEngine
     */
    protected $scoringEngine;

    public function __construct(LeadScoringEngine $scoringEngine)
    {
        $this->scoringEngine = $scoringEngine;
    }

    /**
     * Get the missing required fields for qualifying a lead.
     */
    public function getMissingFields(Lead $lead): array
    {
        $requiredAttributes = config('lead.qualification.required_attributes', []);
        $missing = [];

        foreach ($requiredAttributes as $attributeCode) {
            $value = $lead->{$attributeCode};
            if (is_null($value) || $value === '' || $value === []) {
                $missing[] = $attributeCode;
            }
        }

        return $missing;
    }

    /**
     * Check if a lead can be qualified.
     */
    public function canBeQualified(Lead $lead): bool
    {
        return empty($this->getMissingFields($lead));
    }

    /**
     * Qualify a lead.
     *
     *
     * @throws \Exception
     */
    public function qualify(Lead $lead, ?int $userId = null): Lead
    {
        if ($lead->qualification_status === 'qualified') {
            return $lead; // Already qualified
        }

        if (! $this->canBeQualified($lead)) {
            $missing = implode(', ', $this->getMissingFields($lead));
            throw new \Exception("Cannot qualify lead. Missing required fields: {$missing}");
        }

        Event::dispatch('lead.qualification.started', $lead);

        $oldStatus = $lead->qualification_status;
        $lead->qualification_status = 'qualified';
        Lead::where('id', $lead->id)->update(['qualification_status' => 'qualified']);

        $this->recordQualificationHistory($lead, 'qualified', null, $userId);

        Event::dispatch('lead.qualification.updated', [$lead, $oldStatus, 'qualified']);
        Event::dispatch('lead.qualification.qualified', $lead);

        $this->scoringEngine->evaluateLead($lead);

        return $lead;
    }

    /**
     * Disqualify a lead.
     *
     *
     * @throws \Exception
     */
    public function disqualify(Lead $lead, string $reason, ?int $userId = null): Lead
    {
        if ($lead->qualification_status === 'disqualified') {
            return $lead;
        }

        if (empty(trim($reason))) {
            throw new \Exception('A disqualification reason is required.');
        }

        Event::dispatch('lead.qualification.started', $lead);

        $oldStatus = $lead->qualification_status;
        $lead->qualification_status = 'disqualified';
        Lead::where('id', $lead->id)->update(['qualification_status' => 'disqualified']);

        $this->recordQualificationHistory($lead, 'disqualified', $reason, $userId);

        Event::dispatch('lead.qualification.updated', [$lead, $oldStatus, 'disqualified']);
        Event::dispatch('lead.qualification.disqualified', $lead);

        $this->scoringEngine->evaluateLead($lead);

        return $lead;
    }

    /**
     * Requalify a lead (Move to In Review).
     */
    public function requalify(Lead $lead, ?int $userId = null): Lead
    {
        if ($lead->qualification_status === 'in_review') {
            return $lead;
        }

        Event::dispatch('lead.qualification.started', $lead);

        $oldStatus = $lead->qualification_status;
        $lead->qualification_status = 'in_review';
        Lead::where('id', $lead->id)->update(['qualification_status' => 'in_review']);

        $this->recordQualificationHistory($lead, 'in_review', null, $userId);

        Event::dispatch('lead.qualification.updated', [$lead, $oldStatus, 'in_review']);

        $this->scoringEngine->evaluateLead($lead);

        return $lead;
    }

    /**
     * Record qualification history.
     */
    protected function recordQualificationHistory(Lead $lead, string $status, ?string $reason = null, ?int $userId = null): void
    {
        LeadQualificationProxy::modelClass()::create([
            'lead_id' => $lead->id,
            'user_id' => $userId ?? auth()->guard('user')->user()?->id,
            'status' => $status,
            'reason' => $reason,
        ]);
    }
}
