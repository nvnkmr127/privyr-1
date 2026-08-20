<?php

namespace Webkul\Lead\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Webkul\Lead\Models\LeadProxy;

class EvaluateLeadHealth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lead:evaluate-health';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Evaluates lead inactivity and stage aging, and fires appropriate events.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $batchSize = config('lead_health.batch_size', 100);
        $inactiveDays = config('lead_health.inactivity.inactive_days', 14);
        $needsAttentionDays = config('lead_health.inactivity.needs_attention_days', 7);
        $stuckDays = config('lead_health.stage_aging.stuck_days', 14);

        $this->info('Starting Lead health evaluation...');

        $totalEvaluated = 0;

        LeadProxy::modelClass()::active()->chunkById($batchSize, function ($leads) use ($inactiveDays, $needsAttentionDays, $stuckDays, &$totalEvaluated) {
            foreach ($leads as $lead) {
                $this->evaluateLead($lead, $inactiveDays, $needsAttentionDays, $stuckDays);
                $totalEvaluated++;
            }
        });

        $this->info("Evaluation complete. Processed {$totalEvaluated} leads.");
    }

    /**
     * Evaluate a single lead and dispatch events if thresholds are crossed.
     */
    protected function evaluateLead($lead, $inactiveDays, $needsAttentionDays, $stuckDays)
    {
        // We only fire events if we haven't fired them recently to prevent spam.
        // A robust system would track this in a table, but for now we dispatch the event
        // and let the event listener/automation system handle debouncing or one-off logic.

        $lastActivity = $lead->last_activity_at ?? $lead->created_at;

        if ($lastActivity) {
            $daysSinceActivity = Carbon::parse($lastActivity)->diffInDays(now());

            if ($daysSinceActivity >= $inactiveDays) {
                event('lead.inactivity.detected', [$lead, 'inactive']);
            } elseif ($daysSinceActivity >= $needsAttentionDays) {
                event('lead.inactivity.detected', [$lead, 'needs_attention']);
            }
        }

        $stageChangedAt = $lead->stage_changed_at ?? $lead->created_at;
        if ($stageChangedAt) {
            $stageAge = Carbon::parse($stageChangedAt)->diffInDays(now());
            if ($stageAge >= $stuckDays) {
                event('lead.stage.aging.detected', [$lead, $stageAge]);
            }
        }

        if ($lead->next_follow_up_at && $lead->next_follow_up_at->isPast()) {
            event('lead.followup.overdue', [$lead]);
        }
    }
}
