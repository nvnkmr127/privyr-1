<?php

namespace Webkul\Lead\Services\Sla;

use Carbon\Carbon;
use Illuminate\Support\Facades\Event;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Models\Sla;
use Webkul\Lead\Repositories\LeadSlaRepository;
use Webkul\Lead\Repositories\SlaRepository;
use Webkul\Lead\Contracts\SlaTimeCalculatorInterface;

class LeadSlaService
{
    /**
     * @var SlaRepository
     */
    protected $slaRepository;

    /**
     * @var LeadSlaRepository
     */
    protected $leadSlaRepository;

    /**
     * @var SlaTimeCalculatorInterface
     */
    protected $timeCalculator;

    public function __construct(
        SlaRepository $slaRepository,
        LeadSlaRepository $leadSlaRepository,
        SlaTimeCalculatorInterface $timeCalculator
    ) {
        $this->slaRepository = $slaRepository;
        $this->leadSlaRepository = $leadSlaRepository;
        $this->timeCalculator = $timeCalculator;
    }

    /**
     * Find matching SLA rule for a given lead.
     */
    public function findMatchingSla(Lead $lead): ?Sla
    {
        $slas = $this->slaRepository->findWhere(['is_active' => true])->sortByDesc('sort_order');
        
        foreach ($slas as $sla) {
            $match = true;
            if ($sla->source_id && $sla->source_id != $lead->lead_source_id) $match = false;
            if ($sla->pipeline_id && $sla->pipeline_id != $lead->lead_pipeline_id) $match = false;
            if ($sla->stage_id && $sla->stage_id != $lead->lead_pipeline_stage_id) $match = false;
            if ($sla->type_id && $sla->type_id != $lead->lead_type_id) $match = false;
            if ($sla->team_id && $sla->team_id != $lead->group_id) $match = false;
            
            if ($match) {
                return $sla;
            }
        }
        
        return null;
    }

    /**
     * Handle lead created event. Starts Assignment and First Action SLAs.
     */
    public function handleLeadCreated(Lead $lead)
    {
        $sla = $this->findMatchingSla($lead);
        if (! $sla) return;

        // Start Assignment SLA
        if ($sla->assignment_sla_duration !== null && !$lead->user_id) {
            $this->startSla($lead, $sla, 'Assignment', $sla->assignment_sla_duration);
        }

        // Start First Action SLA
        if ($sla->first_action_sla_duration !== null) {
            $this->startSla($lead, $sla, 'First Action', $sla->first_action_sla_duration);
        }
        
        // Start Stage SLA
        if ($sla->stage_sla_duration !== null) {
            $this->startSla($lead, $sla, 'Stage', $sla->stage_sla_duration);
        }
    }

    /**
     * Handle lead assigned event. Resolves Assignment SLA.
     */
    public function handleLeadAssigned(Lead $lead)
    {
        $this->resolveSla($lead, 'Assignment', $lead->user_id, $lead->group_id);
    }

    /**
     * Handle meaningful activity created. Resolves First Action and Follow-up SLAs.
     */
    public function handleActivityCreated(Lead $lead, $activity)
    {
        // Must be a meaningful activity (not system)
        if (in_array($activity->type ?? 'note', ['call', 'meeting', 'email', 'whatsapp', 'note', 'task'])) {
            $this->resolveSla($lead, 'First Action', $activity->user_id);
        }
    }

    /**
     * Handle follow-up completed.
     */
    public function handleFollowUpCompleted(Lead $lead, $activity)
    {
        $this->resolveSla($lead, 'Follow-up', $activity->user_id);
    }
    
    /**
     * Handle follow-up due. Starts Follow-up SLA.
     */
    public function handleFollowUpDue(Lead $lead, $dueDate)
    {
        $sla = $this->findMatchingSla($lead);
        if (! $sla || $sla->follow_up_sla_duration === null) return;
        
        $this->startSla($lead, $sla, 'Follow-up', $sla->follow_up_sla_duration, $dueDate);
    }

    /**
     * Handle stage updated. Resolves old Stage SLA and starts new one.
     */
    public function handleStageUpdated(Lead $lead)
    {
        // Resolve previous stage SLA
        $this->resolveSla($lead, 'Stage');
        
        $sla = $this->findMatchingSla($lead);
        if (! $sla || $sla->stage_sla_duration === null) return;
        
        // Skip Nurturing, Converted, Lost logic here depending on config
        if (in_array($lead->status, ['Converted', 'Lost', 'Junk'])) {
            // Do not start new SLA
            return;
        }
        
        if ($lead->status === 'Nurturing') {
            // Paused or do not start. For now, do not start new Stage SLA
            return;
        }
        
        $this->startSla($lead, $sla, 'Stage', $sla->stage_sla_duration);
    }

    /**
     * Start a new SLA tracking record.
     */
    public function startSla(Lead $lead, Sla $sla, string $type, int $durationMinutes, Carbon $startAt = null)
    {
        // Close any existing On Track SLA of same type
        $existing = $this->leadSlaRepository->findOneWhere([
            'lead_id' => $lead->id,
            'sla_type' => $type,
            'status' => 'On Track'
        ]);
        
        if ($existing) {
            return $existing; // Already active, avoid duplicate
        }

        $now = $startAt ?? Carbon::now();
        $dueAt = $this->timeCalculator->calculateDueDate($now, $durationMinutes);

        $leadSla = $this->leadSlaRepository->create([
            'lead_id' => $lead->id,
            'sla_id' => $sla->id,
            'sla_type' => $type,
            'status' => 'On Track',
            'started_at' => $now,
            'due_at' => $dueAt,
            'owner_id' => $lead->user_id,
            'team_id' => $lead->group_id,
            'stage_id' => $lead->lead_pipeline_stage_id,
        ]);
        
        Event::dispatch('lead.sla.started', $leadSla);
        
        return $leadSla;
    }

    /**
     * Resolve an active SLA tracking record.
     */
    public function resolveSla(Lead $lead, string $type, $ownerId = null, $teamId = null)
    {
        // Find active SLAs (On Track or Breached or Due Soon)
        $activeSlas = $this->leadSlaRepository->findWhere([
            'lead_id' => $lead->id,
            'sla_type' => $type,
        ])->whereIn('status', ['On Track', 'Due Soon', 'Breached']);
        
        foreach ($activeSlas as $leadSla) {
            $this->leadSlaRepository->update([
                'status' => 'Resolved',
                'resolved_at' => Carbon::now(),
                // Keep the owner that resolved it if passed
            ], $leadSla->id);
            
            Event::dispatch('lead.sla.resolved', $leadSla);
        }
    }

    /**
     * Resolve all active SLA tracking records.
     */
    public function resolveAllSlas(Lead $lead)
    {
        $activeSlas = $this->leadSlaRepository->findWhere([
            'lead_id' => $lead->id,
        ])->whereIn('status', ['On Track', 'Due Soon', 'Breached']);

        foreach ($activeSlas as $leadSla) {
            $this->leadSlaRepository->update([
                'status' => 'Resolved',
                'resolved_at' => Carbon::now(),
            ], $leadSla->id);

            Event::dispatch('lead.sla.resolved', $leadSla);
        }
    }
}
