<?php

use Carbon\Carbon;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Models\Stage;
use Webkul\Activity\Models\Activity;

it('logs system events correctly via observers', function () {
    $this->loginAsAdmin();
    $repo = app(\Webkul\Lead\Repositories\LeadRepository::class);

    // Create a lead
    $lead = $repo->create([
        'title' => 'Timeline Test Lead',
        'entity_type' => 'leads',
        'lead_pipeline_id' => 1,
        'lead_pipeline_stage_id' => 1,
        'expected_close_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
        'lead_value' => 5000,
    ]);

    // Update the lead (change stage, owner, qual, value, close date, score)
    $repo->update([
        'entity_type' => 'leads',
        'lead_pipeline_stage_id' => 2,
        'user_id' => auth()->id(),
        'qualification_status' => 'qualified',
        'expected_close_date' => Carbon::now()->addDays(10)->format('Y-m-d'),
        'lead_value' => 10000,
    ], $lead->id);

    // Refresh
    $lead->refresh();
    
    // Direct DB update to trigger score change observer since Engine overrides
    $lead->lead_score = 99;
    $lead->save();

    // The observer should have created system activities
    $systemActivities = $lead->activities()->where('type', 'system')->get();
    
    expect($systemActivities->count())->toBeGreaterThanOrEqual(1);

    $titles = $systemActivities->pluck('title')->toArray();
    expect($titles)->toContain('Lead Created');
    expect($titles)->toContain('Stage Changed');
    expect($titles)->toContain('Owner Changed');
    expect($titles)->toContain('Qualification Changed');
    expect($titles)->toContain('Lead Value Changed');
    expect($titles)->toContain('Score Updated');

    // Test the timeline formatter
    $timeline = $lead->getChronologicalTimeline();
    
    $systemEvents = $timeline->where('type', 'activity_system');
    
    expect($systemEvents->count())->toBeGreaterThanOrEqual(6);
    
    $stageEvent = $systemEvents->firstWhere('title', 'Stage Changed');
    expect($stageEvent['description'])->toContain('Changed from');
});
