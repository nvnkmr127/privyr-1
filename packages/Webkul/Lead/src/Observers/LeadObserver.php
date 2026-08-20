<?php

namespace Webkul\Lead\Observers;

use Carbon\Carbon;
use Webkul\Activity\Services\SystemActivityLogger;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Models\LeadStageHistoryProxy;
use Webkul\Lead\Repositories\StageRepository;
use Webkul\User\Repositories\UserRepository;

class LeadObserver
{
    /**
     * Handle the Lead "creating" event.
     */
    public function creating(Lead $lead): void
    {
        if (! $lead->stage_changed_at) {
            $lead->stage_changed_at = Carbon::now();
        }
    }

    /**
     * Handle the Lead "created" event.
     */
    public function created(Lead $lead): void
    {
        $logger = app(SystemActivityLogger::class);
        $logger->log($lead, 'Lead Created', [
            'event' => 'lead_created',
        ]);
    }

    /**
     * Handle the Lead "updating" event.
     */
    public function updating(Lead $lead): void
    {
        if ($lead->isDirty('lead_pipeline_stage_id')) {
            $lead->stage_changed_at = Carbon::now();
        }
    }

    /**
     * Handle the Lead "updated" event.
     */
    public function updated(Lead $lead): void
    {
        $logger = app(SystemActivityLogger::class);

        $changes = $lead->getChanges();
        $original = $lead->getOriginal();

        // Stage Change
        if (array_key_exists('lead_pipeline_stage_id', $changes)) {
            $oldStage = app(StageRepository::class)->find($original['lead_pipeline_stage_id'])?->name ?? 'Unknown';
            $newStage = app(StageRepository::class)->find($changes['lead_pipeline_stage_id'])?->name ?? 'Unknown';

            $logger->log($lead, 'Stage Changed', [
                'event' => 'stage_change',
                'old' => $oldStage,
                'new' => $newStage,
            ]);

            // Create Stage History
            LeadStageHistoryProxy::modelClass()::create([
                'lead_id' => $lead->id,
                'pipeline_id' => $lead->lead_pipeline_id,
                'previous_stage_id' => $original['lead_pipeline_stage_id'] ?? null,
                'new_stage_id' => $changes['lead_pipeline_stage_id'],
                'changed_by_id' => auth()->guard('user')->user()?->id,
            ]);
        }

        // Owner Change
        if (array_key_exists('user_id', $changes)) {
            $oldUser = $original['user_id'] ? app(UserRepository::class)->find($original['user_id'])?->name : 'Unassigned';
            $newUser = $changes['user_id'] ? app(UserRepository::class)->find($changes['user_id'])?->name : 'Unassigned';

            $logger->log($lead, 'Owner Changed', [
                'event' => 'owner_change',
                'old' => $oldUser,
                'new' => $newUser,
            ]);
        }

        // Qualification Change
        if (array_key_exists('qualification_status', $changes)) {
            $oldStatus = $original['qualification_status'] ?? 'None';
            $newStatus = $changes['qualification_status'] ?? 'None';

            $logger->log($lead, 'Qualification Changed', [
                'event' => 'qualification_change',
                'old' => ucfirst($oldStatus),
                'new' => ucfirst($newStatus),
            ]);
        }

        // Score Change (only log if it actually changed by more than 0, which getChanges handles)
        if (array_key_exists('lead_score', $changes)) {
            $oldScore = $original['lead_score'] ?? 0;
            $newScore = $changes['lead_score'] ?? 0;

            $logger->log($lead, 'Score Updated', [
                'event' => 'score_change',
                'old' => $oldScore,
                'new' => $newScore,
            ]);
        }

        // Expected Close Date Change
        if (array_key_exists('expected_close_date', $changes)) {
            $oldDate = $original['expected_close_date'] ? Carbon::parse($original['expected_close_date'])->format('M d, Y') : 'None';
            $newDate = $changes['expected_close_date'] ? Carbon::parse($changes['expected_close_date'])->format('M d, Y') : 'None';

            $logger->log($lead, 'Expected Close Date Changed', [
                'event' => 'close_date_change',
                'old' => $oldDate,
                'new' => $newDate,
            ]);
        }

        // Value Change
        if (array_key_exists('lead_value', $changes)) {
            $oldVal = $original['lead_value'] ?? 0;
            $newVal = $changes['lead_value'] ?? 0;

            $logger->log($lead, 'Lead Value Changed', [
                'event' => 'value_change',
                'old' => core()->formatBasePrice($oldVal),
                'new' => core()->formatBasePrice($newVal),
            ]);
        }
    }
}
