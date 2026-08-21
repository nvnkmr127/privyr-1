<?php

namespace Webkul\Lead\Tests\Feature;

use Illuminate\Console\Scheduling\Schedule;
use Tests\TestCase;
use Webkul\Lead\Jobs\EvaluateLeadSlasJob;

class SlaScheduleTest extends TestCase
{
    /** @test */
    public function it_schedules_the_sla_evaluation_job_every_minute()
    {
        $schedule = $this->app->make(Schedule::class);

        $events = collect($schedule->events());

        $hasSlaJob = $events->contains(function ($event) {
            return str_contains($event->description, EvaluateLeadSlasJob::class)
                && $event->expression === '* * * * *'; // Every minute
        });

        $this->assertTrue($hasSlaJob, 'The EvaluateLeadSlasJob is not scheduled to run every minute.');
    }
}
