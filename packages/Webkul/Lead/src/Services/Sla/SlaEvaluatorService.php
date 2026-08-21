<?php

namespace Webkul\Lead\Services\Sla;

use Carbon\Carbon;
use Illuminate\Support\Facades\Event;
use Webkul\Lead\Repositories\LeadSlaRepository;

class SlaEvaluatorService
{
    /**
     * @var LeadSlaRepository
     */
    protected $leadSlaRepository;

    public function __construct(LeadSlaRepository $leadSlaRepository)
    {
        $this->leadSlaRepository = $leadSlaRepository;
    }

    /**
     * Evaluate active SLAs and mark them as Due Soon or Breached.
     */
    public function evaluate()
    {
        $now = Carbon::now();
        $dueSoonThreshold = config('lead.sla.due_soon_threshold_minutes', 15);
        $dueSoonTime = $now->copy()->addMinutes($dueSoonThreshold);

        // Find SLAs that are On Track but due soon
        $dueSoonSlas = $this->leadSlaRepository->findDueSoonSlas($dueSoonTime, $now);

        foreach ($dueSoonSlas as $leadSla) {
            $this->leadSlaRepository->update([
                'status' => 'Due Soon',
            ], $leadSla->id);

            Event::dispatch('lead.sla.due_soon', $leadSla);
        }

        // Find SLAs that are On Track or Due Soon and are past due
        $breachedSlas = $this->leadSlaRepository->findBreachedSlas($now);

        foreach ($breachedSlas as $leadSla) {
            $this->leadSlaRepository->update([
                'status' => 'Breached',
                'breached_at' => $now,
            ], $leadSla->id);

            Event::dispatch('lead.sla.breached', $leadSla);
        }
    }
}
