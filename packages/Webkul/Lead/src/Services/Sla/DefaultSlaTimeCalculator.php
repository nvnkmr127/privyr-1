<?php

namespace Webkul\Lead\Services\Sla;

use Carbon\Carbon;
use Webkul\Lead\Contracts\SlaTimeCalculatorInterface;

class DefaultSlaTimeCalculator implements SlaTimeCalculatorInterface
{
    public function calculateDueDate(Carbon $start, int $durationMinutes): Carbon
    {
        // For now, this is a simple 24/7 calculator.
        // It can be replaced later with a business-hours aware calculator.
        return $start->copy()->addMinutes($durationMinutes);
    }
}
