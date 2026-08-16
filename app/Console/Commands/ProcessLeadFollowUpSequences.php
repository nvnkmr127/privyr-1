<?php

namespace App\Console\Commands;

use App\Services\LeadFollowUpSequenceService;
use Illuminate\Console\Command;

class ProcessLeadFollowUpSequences extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lead:process-sequences';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process multi-step follow-up drip sequences and trackable file messages for active leads';

    /**
     * Execute the console command.
     */
    public function handle(LeadFollowUpSequenceService $sequenceService)
    {
        $this->info('Processing multi-step lead follow-up sequences...');
        $processed = $sequenceService->processPendingSteps();
        $this->info("Completed processing: {$processed} sequence steps dispatched.");

        return 0;
    }
}
