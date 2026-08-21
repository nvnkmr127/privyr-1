<?php

use Webkul\Activity\Models\Activity;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Models\Pipeline;
use Webkul\Lead\Models\Stage;

it('renders the activity timeline with responsive two-column proportions and sticky sidebar', function () {
    $this->loginAsAdmin();
    $admin = auth()->guard('user')->user();

    $pipeline = Pipeline::first() ?? Pipeline::create(['name' => 'Default']);
    $stage = Stage::first() ?? Stage::create(['name' => 'New', 'code' => 'new', 'lead_pipeline_id' => $pipeline->id]);
    $lead = Lead::create([
        'title' => 'Test Lead',
        'lead_pipeline_id' => $pipeline->id,
        'lead_pipeline_stage_id' => $stage->id,
        'user_id' => $admin->id,
    ]);

    $response = $this->get(route('admin.leads.view', $lead->id));

    $response->assertOk()
        ->assertSee('lg:col-span-4 lg:sticky lg:top-4', false)
        ->assertSee('lg:col-span-8 flex flex-col', false)
        ->assertSee('Chronological Activity Timeline')
        ->assertSee('timeline-filters', false);
});

it('renders chronological timeline events with category filter buttons and badges', function () {
    $this->loginAsAdmin();
    $admin = auth()->guard('user')->user();

    $pipeline = Pipeline::first() ?? Pipeline::create(['name' => 'Default']);
    $stage = Stage::first() ?? Stage::create(['name' => 'New', 'code' => 'new', 'lead_pipeline_id' => $pipeline->id]);
    $lead = Lead::create([
        'title' => 'Test Lead',
        'lead_pipeline_id' => $pipeline->id,
        'lead_pipeline_stage_id' => $stage->id,
        'user_id' => $admin->id,
    ]);

    // Attach a call activity with long description
    $activity = Activity::create([
        'title' => 'Project Kickoff Call with Client',
        'type' => 'call',
        'comment' => str_repeat('Detailed discussion regarding requirements and milestones. ', 10),
        'user_id' => $admin->id,
        'lead_id' => $lead->id,
        'is_done' => 1,
    ]);

    $response = $this->get(route('admin.leads.view', $lead->id));

    $response->assertOk()
        ->assertSee('Project Kickoff Call with Client')
        ->assertSee('filterTimeline', false)
        ->assertSee('toggleTimelineDesc', false)
        ->assertSee('Read more', false);
});
