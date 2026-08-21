<?php

namespace Webkul\Lead\Services;

use Illuminate\Support\Facades\Event;
use Webkul\Lead\Contracts\Lead;
use Webkul\Lead\Repositories\LeadAssignmentRepository;
use Webkul\Lead\Repositories\LeadQualificationRepository;
use Webkul\Lead\Repositories\LeadRepository;

class LeadManagementService
{
    public function __construct(
        protected LeadRepository $leadRepository,
        protected LeadDuplicateService $leadDuplicateService,
        protected LeadAssignmentService $leadAssignmentService,
        protected LeadAssignmentRepository $leadAssignmentRepository,
        protected LeadQualificationRepository $leadQualificationRepository,
        protected LeadScoringEngine $leadScoringEngine,
        protected LeadDataQualityService $leadDataQualityService
    ) {}

    /**
     * Create lead data and orchestrate side effects.
     */
    public function createLead(array $data): Lead
    {
        // Normalize Email and Phone
        if (isset($data['emails']) && is_array($data['emails']) && count($data['emails']) > 0) {
            $data['normalized_primary_email'] = $this->leadDuplicateService->normalizeEmail($data['emails'][0]['value'] ?? null);
        }

        if (isset($data['phones']) && is_array($data['phones']) && count($data['phones']) > 0) {
            $data['normalized_primary_phone'] = $this->leadDuplicateService->normalizePhone($data['phones'][0]['value'] ?? null);
        }

        $lead = $this->leadRepository->create($data);

        Event::dispatch('lead.create.after', $lead);

        if (! empty($lead->qualification_status)) {
            $this->leadQualificationRepository->create([
                'lead_id' => $lead->id,
                'status' => $lead->qualification_status,
                'reason' => $data['qualification_reason'] ?? null,
                'user_id' => auth()->check() ? auth()->id() : null,
            ]);

            Event::dispatch('lead.qualification.started', $lead);

            if ($lead->qualification_status === 'qualified') {
                Event::dispatch('lead.qualification.qualified', $lead);
            } elseif ($lead->qualification_status === 'disqualified') {
                Event::dispatch('lead.qualification.disqualified', $lead);
            }
            Event::dispatch('lead.qualification.updated', $lead);
        }

        // Assignment logic
        if (empty($lead->user_id)) {
            $lead->refresh();
            // Try to auto-assign
            $this->leadAssignmentService->assignLead($lead);
        } else {
            // Manual assignment during creation
            $this->leadAssignmentRepository->create([
                'lead_id' => $lead->id,
                'assigned_to' => $lead->user_id,
                'assigned_by' => auth()->check() ? auth()->id() : null,
                'previous_owner' => null,
                'reason' => $data['assignment_reason'] ?? 'Manual Assignment',
            ]);
        }

        // Evaluate Lead Score
        $this->leadScoringEngine->evaluateLead($lead);

        // Calculate Data Quality State
        $this->leadDataQualityService->calculateQualityState($lead);

        return $lead;
    }

    /**
     * Update lead data and orchestrate side effects.
     */
    public function updateLead(Lead $lead, array $data, $attributes = []): Lead
    {
        $originalUserId = $lead->user_id;
        $originalQualificationStatus = $lead->qualification_status;
        $originalStageId = $lead->lead_pipeline_stage_id;
        $originalStatus = $lead->status;

        // Perform the repository update (which prevents direct lifecycle manipulation)
        $lead = $this->leadRepository->update($data, $lead->id, $attributes);

        // Qualification Orchestration
        if ($lead->qualification_status !== $originalQualificationStatus) {
            $this->leadQualificationRepository->create([
                'lead_id' => $lead->id,
                'status' => $lead->qualification_status,
                'reason' => $data['qualification_reason'] ?? null,
                'user_id' => auth()->check() ? auth()->id() : null,
            ]);

            if (empty($originalQualificationStatus) && ! empty($lead->qualification_status)) {
                Event::dispatch('lead.qualification.started', $lead);
            }

            if ($lead->qualification_status === 'qualified') {
                Event::dispatch('lead.qualification.qualified', $lead);
            } elseif ($lead->qualification_status === 'disqualified') {
                Event::dispatch('lead.qualification.disqualified', $lead);
            } elseif ($lead->qualification_status === 'unqualified' && in_array($originalQualificationStatus, ['qualified', 'disqualified'])) {
                Event::dispatch('lead.qualification.reopened', $lead);
            }

            Event::dispatch('lead.qualification.updated', $lead);
        }

        // Assignment Orchestration
        if ($lead->user_id !== $originalUserId) {
            $this->leadAssignmentRepository->create([
                'lead_id' => $lead->id,
                'assigned_to' => $lead->user_id,
                'assigned_by' => auth()->check() ? auth()->id() : null,
                'previous_owner' => $originalUserId,
                'reason' => $data['assignment_reason'] ?? 'Manual Reassignment',
            ]);
        }

        // Evaluate Lead Score
        $this->leadScoringEngine->evaluateLead($lead);

        // Calculate Data Quality State
        $this->leadDataQualityService->calculateQualityState($lead);

        return $lead;
    }
}
