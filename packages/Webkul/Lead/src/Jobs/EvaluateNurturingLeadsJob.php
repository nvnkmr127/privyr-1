<?php

namespace Webkul\Lead\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Event;
use Webkul\Lead\Repositories\LeadRepository;

class EvaluateNurturingLeadsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(LeadRepository $leadRepository)
    {
        $leads = $leadRepository->findWhere([
            'status' => 'Nurturing',
            ['nurture_reengagement_date', '<=', Carbon::today()],
        ]);

        foreach ($leads as $lead) {
            Event::dispatch('lead.nurturing.due', $lead);
        }
    }
}
