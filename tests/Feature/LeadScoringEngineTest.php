<?php

use Carbon\Carbon;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Models\LeadScoreRule;
use Webkul\Lead\Repositories\LeadRepository;
use Webkul\Lead\Services\LeadScoringEngine;

it('calculates score based on dynamic rules', function () {
    $this->loginAsAdmin();
    $repo = app(LeadRepository::class);

    // Create rules
    LeadScoreRule::create([
        'name' => 'Qualified Status',
        'type' => 'attribute',
        'conditions' => ['attribute' => 'qualification_status', 'operator' => '==', 'value' => 'qualified'],
        'points' => 25,
    ]);

    LeadScoreRule::create([
        'name' => 'Has Name',
        'type' => 'attribute',
        'conditions' => ['attribute' => 'person_name', 'operator' => 'not_null'],
        'points' => 10,
    ]);

    LeadScoreRule::create([
        'name' => 'Stale Lead Penalty',
        'type' => 'recency',
        'conditions' => ['days_since_activity' => 7],
        'points' => -20,
    ]);

    // Create lead
    $lead = $repo->create([
        'title' => 'Test Scoring Lead',
        'entity_type' => 'leads',
        'lead_pipeline_id' => 1,
        'lead_pipeline_stage_id' => 1,
        'person_name' => 'John Doe',
        'qualification_status' => 'qualified',
        'last_contacted_at' => Carbon::now()->subDays(10), // Triggers stale penalty
    ]);

    $engine = app(LeadScoringEngine::class);
    $engine->evaluateLead($lead);

    // Expected: 25 (Qualified) + 10 (Name) - 20 (Stale) = 15
    expect($lead->lead_score)->toEqual(15);
    expect($lead->scoreLogs()->count())->toEqual(3);

    $reasons = $lead->scoreLogs()->pluck('reason')->toArray();
    expect($reasons)->toContain('Qualified Status (+25)');
    expect($reasons)->toContain('Has Name (+10)');
    expect($reasons)->toContain('Stale Lead Penalty (-20)');
});

it('evaluates health state correctly', function () {
    $this->loginAsAdmin();
    $repo = app(LeadRepository::class);

    $lead = $repo->create([
        'title' => 'Hot Lead',
        'entity_type' => 'leads',
        'lead_pipeline_id' => 1,
        'lead_pipeline_stage_id' => 1,
        'last_contacted_at' => Carbon::now(),
    ]);

    $lead->getConnection()->table('leads')->where('id', $lead->id)->update(['lead_score' => 85]);
    $lead->refresh();

    expect($lead->health_state)->toEqual('hot');

    $lead->getConnection()->table('leads')->where('id', $lead->id)->update(['lead_score' => 60]);
    $lead->refresh();
    expect($lead->health_state)->toEqual('warm');

    $lead->getConnection()->table('leads')->where('id', $lead->id)->update(['lead_score' => 20]);
    $lead->refresh();
    expect($lead->health_state)->toEqual('cold');

    // Test overrides
    $lead->getConnection()->table('leads')->where('id', $lead->id)->update([
        'lead_score' => 90,
        'last_contacted_at' => Carbon::now()->subDays(8),
    ]);
    $lead->refresh();
    expect($lead->health_state)->toEqual('at_risk');

    $lead->getConnection()->table('leads')->where('id', $lead->id)->update([
        'lead_score' => 90,
        'last_contacted_at' => Carbon::now()->subDays(15),
    ]);
    $lead->refresh();
    expect($lead->health_state)->toEqual('stale');
});
