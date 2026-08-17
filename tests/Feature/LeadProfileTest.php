<?php

use Webkul\Lead\Models\Lead;
use Webkul\Lead\Models\Pipeline;
use Webkul\Lead\Models\Stage;

it('renders the lead profile page with master timeline', function () {
    $this->loginAsAdmin();

    $pipeline = Pipeline::first();
    $stage = Stage::first();

    $lead = Lead::create([
        'title' => 'Test Profile Lead',
        'lead_score' => 85,
        'utm_source' => 'facebook',
        'utm_medium' => 'cpc',
        'utm_campaign' => 'summer_sale',
        'lead_pipeline_id' => $pipeline->id,
        'lead_pipeline_stage_id' => $stage->id,
        'status' => 1,
    ]);

    $response = $this->get(route('admin.leads.view', $lead->id));

    $response->assertOk()
        ->assertSee('Test Profile Lead')
        ->assertSee('Chronological Activity Timeline');
});

it('returns formatted chronological timeline events for a lead', function () {
    $pipeline = Pipeline::first();
    $stage = Stage::first();

    $lead = Lead::create([
        'title' => 'Timeline Lead',
        'lead_pipeline_id' => $pipeline->id,
        'lead_pipeline_stage_id' => $stage->id,
        'status' => 1,
    ]);

    $timeline = $lead->getChronologicalTimeline();

    expect($timeline)->not->toBeEmpty();
    expect($timeline->first()['type'])->toBe('lead_created');
});
