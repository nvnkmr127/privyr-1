<?php

use Webkul\Lead\Models\Lead;
use Webkul\Lead\Models\Pipeline;
use Webkul\Lead\Models\Source;
use Webkul\Lead\Models\Stage;
use Webkul\User\Models\User;

it('renders the compact lead header on lead details page', function () {
    $admin = User::first();
    $this->actingAs($admin);

    $pipeline = Pipeline::first();
    $stage = Stage::first();
    $source = Source::first();

    $lead = Lead::create([
        'title' => 'Executive Office Fitout - Acme Corp',
        'lead_value' => 50000,
        'user_id' => $admin->id,
        'lead_pipeline_id' => $pipeline?->id ?? 1,
        'lead_pipeline_stage_id' => $stage?->id ?? 1,
        'lead_source_id' => $source?->id ?? 1,
        'status' => 1,
    ]);

    $response = $this->get(route('admin.leads.view', $lead->id));

    $response->assertOk()
        ->assertSee('Executive Office Fitout - Acme Corp')
        ->assertSee($admin->name)
        ->assertSee('Pipeline')
        ->assertSee('Source')
        ->assertSee('Owner')
        ->assertSee('Status');
});
