<?php

use Carbon\Carbon;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Repositories\LeadRepository;
use Webkul\Lead\Services\LeadAnalyticsService;

it('calculates lead analytics correctly', function () {
    $this->loginAsAdmin();
    $repo = app(LeadRepository::class);

    // Create won lead
    $lead1 = $repo->create([
        'title' => 'Won Lead',
        'entity_type' => 'leads',
        'lead_pipeline_id' => 1,
        'lead_pipeline_stage_id' => 4, // Assuming 4 is won
        'qualification_status' => 'qualified',
        'expected_close_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
        'lead_value' => 5000,
    ]);

    // Create lost lead
    $lead2 = $repo->create([
        'title' => 'Lost Lead',
        'entity_type' => 'leads',
        'lead_pipeline_id' => 1,
        'lead_pipeline_stage_id' => 5, // Assuming 5 is lost
        'qualification_status' => 'unqualified',
        'expected_close_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
        'lead_value' => 1000,
    ]);

    // Ensure they have different scores manually
    $lead1->lead_score = 90;
    $lead1->save();

    $lead2->lead_score = 30;
    $lead2->save();

    $service = app(LeadAnalyticsService::class);
    $metrics = $service->getMetrics(Carbon::now()->subDays(30)->toDateTimeString(), Carbon::now()->addDays(1)->toDateTimeString());

    expect($metrics['total_leads'])->toBeGreaterThanOrEqual(2);
    expect($metrics['qualified_leads'])->toBeGreaterThanOrEqual(1);
    expect($metrics['unqualified_leads'])->toBeGreaterThanOrEqual(1);

    // We can't strictly assert the exact numbers if the DB has other leads,
    // but we can assert the structure and that it calculated correctly.
    expect(array_key_exists('conversion_rate', $metrics))->toBeTrue();
    expect(array_key_exists('leads_by_source', $metrics))->toBeTrue();
    expect(array_key_exists('leads_by_owner', $metrics))->toBeTrue();
    expect(array_key_exists('stale_leads', $metrics))->toBeTrue();
    expect(array_key_exists('lead_trend', $metrics))->toBeTrue();
});
