<?php

namespace Webkul\Lead\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Webkul\Lead\Models\Lead;

class ProcessFollowUps extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lead:process-follow-ups';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process overdue follow-ups and trigger necessary events.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Starting follow-up processing...');

        // Find leads with next_follow_up_at in the past, but not yet marked as overdue
        // Wait, there's no `is_follow_up_overdue` flag on Lead, so we might trigger this
        // multiple times. To avoid this, we can rely on Activity. We'll check Activities
        // that are pending, schedule_from is past, and we haven't triggered overdue yet.
        // Let's add a flag on Activity instead or use an existing one if it exists.
        // For simplicity and since we don't have a flag, let's just trigger it once per activity
        // using a JSON flag in `additional`.

        $activities = \Webkul\Activity\Models\Activity::where('status', 'pending')
            ->where('schedule_from', '<', Carbon::now())
            ->whereNull('additional->is_overdue_triggered')
            ->with('lead')
            ->get();

        $count = 0;
        foreach ($activities as $activity) {
            if ($activity->lead) {
                // Fire automation/notification events
                event('lead.follow_up.overdue', $activity->lead);
                
                // Mark as triggered so we don't fire again
                $additional = $activity->additional ?? [];
                $additional['is_overdue_triggered'] = true;
                $activity->update(['additional' => $additional]);
                
                $count++;
            }
        }

        $this->info("Processed $count overdue follow-ups.");
    }
}
