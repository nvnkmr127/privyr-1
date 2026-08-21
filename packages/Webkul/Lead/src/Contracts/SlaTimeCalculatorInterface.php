<?php

namespace Webkul\Lead\Contracts;

use Carbon\Carbon;

interface SlaTimeCalculatorInterface
{
    /**
     * Calculate the due date based on the start date and duration in minutes.
     */
    public function calculateDueDate(Carbon $start, int $durationMinutes): Carbon;
}
