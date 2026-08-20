<?php

namespace Webkul\Lead\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Webkul\Activity\Repositories\ActivityRepository;
use Webkul\Lead\Repositories\LeadRepository;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Models\LeadNurtureHistoryProxy;

class LeadNurtureService
{
    public function __construct(
        protected LeadRepository $leadRepository,
        protected ActivityRepository $activityRepository
    ) {}

    /**
     * Move a lead into Nurturing status.
     *
     * @param Lead $lead
     * @param array $data Expected: nurture_reason_id, nurture_reengagement_date, nurture_notes
     */
    public function startNurturing(Lead $lead, array $data): Lead
    {
        DB::beginTransaction();

        try {
            $previousStatus = $lead->status;

            // 1. Update Lead Status and Nurture Fields
            $leadData = [
                'status' => 'Nurturing',
                'nurture_reason_id' => $data['nurture_reason_id'] ?? null,
                'nurtured_at' => Carbon::now(),
                'nurture_reengagement_date' => $data['nurture_reengagement_date'],
                'nurture_notes' => $data['nurture_notes'] ?? null,
            ];

            // 2. Create Follow-up Activity if requested
            if (!empty($data['create_follow_up'])) {
                $this->activityRepository->create([
                    'type' => 'task',
                    'title' => 'Re-engage Nurtured Lead',
                    'comment' => $data['nurture_notes'] ?? 'Lead is ready for re-engagement',
                    'schedule_from' => Carbon::parse($data['nurture_reengagement_date'])->startOfDay(),
                    'schedule_to' => Carbon::parse($data['nurture_reengagement_date'])->endOfDay(),
                    'is_done' => 0,
                    'user_id' => $lead->user_id ?? auth()->id(),
                    'lead_id' => $lead->id,
                ]);

                // Update lead next follow up at
                $leadData['next_follow_up_at'] = Carbon::parse($data['nurture_reengagement_date'])->startOfDay();
            }

            $lead = $this->leadRepository->update($leadData, $lead->id);

            // 3. Create Nurture History
            LeadNurtureHistoryProxy::create([
                'lead_id' => $lead->id,
                'previous_status' => $previousStatus,
                'nurture_reason_id' => $leadData['nurture_reason_id'],
                'started_at' => $leadData['nurtured_at'],
                'expected_reengagement_date' => $leadData['nurture_reengagement_date'],
                'user_id' => auth()->id(),
                'notes' => $leadData['nurture_notes'],
            ]);

            // 4. Log to System Activity
            $this->activityRepository->create([
                'type' => 'system',
                'title' => 'Lead moved to Nurturing',
                'comment' => 'Reason: ' . ($lead->nurture_reason_id ? 'Configured Reason' : 'Not specified') . 
                             '. Re-engagement scheduled for: ' . Carbon::parse($leadData['nurture_reengagement_date'])->format('M d, Y'),
                'is_done' => 1,
                'user_id' => auth()->id(),
                'lead_id' => $lead->id,
            ]);

            Event::dispatch('lead.nurturing.started', $lead);

            DB::commit();

            return $lead;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Complete a lead's nurture cycle.
     *
     * @param Lead $lead
     * @param string $outcome (Working, Qualified, Converted, Lost, Junk)
     */
    public function completeNurturing(Lead $lead, string $outcome, ?string $notes = null): Lead
    {
        DB::beginTransaction();

        try {
            // Find active nurture history
            $activeHistory = $lead->nurtureHistories()->whereNull('ended_at')->latest()->first();

            if ($activeHistory) {
                $activeHistory->update([
                    'ended_at' => Carbon::now(),
                    'end_reason' => $outcome,
                ]);
            }

            // Update Lead
            $leadData = [
                'status' => $outcome,
                'nurture_reason_id' => null,
                'nurtured_at' => null,
                'nurture_reengagement_date' => null,
                'nurture_notes' => null,
            ];

            $lead = $this->leadRepository->update($leadData, $lead->id);

            // Log to System Activity
            $this->activityRepository->create([
                'type' => 'system',
                'title' => 'Lead re-engaged from Nurturing',
                'comment' => 'Outcome: ' . $outcome . ($notes ? '. Notes: ' . $notes : ''),
                'is_done' => 1,
                'user_id' => auth()->id(),
                'lead_id' => $lead->id,
            ]);

            Event::dispatch('lead.nurturing.completed', ['lead' => $lead, 'outcome' => $outcome]);

            DB::commit();

            return $lead;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
