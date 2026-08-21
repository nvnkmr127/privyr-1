<?php

use Webkul\Lead\Models\LeadQualification;
use Webkul\Lead\Models\Pipeline;
use Webkul\Lead\Models\Stage;
use Webkul\Lead\Repositories\LeadRepository;

it('identifies missing required fields', function () {
    $this->loginAsAdmin();
    config(['lead.qualification.required_attributes' => ['budget', 'timeline']]);

    $pipeline = Pipeline::first() ?? Pipeline::create(['name' => 'Default']);
    $stage = Stage::first() ?? Stage::create(['name' => 'New', 'code' => 'new', 'lead_pipeline_id' => $pipeline->id]);

    $lead = app(LeadRepository::class)->create([
        'title' => 'Test Lead',
        'lead_pipeline_id' => $pipeline->id,
        'lead_pipeline_stage_id' => $stage->id,
        'budget' => null,
        'timeline' => null,
    ]);

    $response = $this->postJson(route('admin.leads.qualify', $lead->id));
    $response->assertStatus(400);
});

it('qualifies a lead successfully when requirements are met', function () {
    $this->loginAsAdmin();
    config(['lead.qualification.required_attributes' => ['budget']]);

    $pipeline = Pipeline::first() ?? Pipeline::create(['name' => 'Default']);
    $stage = Stage::first() ?? Stage::create(['name' => 'New', 'code' => 'new', 'lead_pipeline_id' => $pipeline->id]);

    $lead = app(LeadRepository::class)->create([
        'title' => 'Test Lead',
        'lead_pipeline_id' => $pipeline->id,
        'lead_pipeline_stage_id' => $stage->id,
        'budget' => '1000',
        'qualification_status' => 'in_review',
    ]);

    $response = $this->postJson(route('admin.leads.qualify', $lead->id));
    $response->assertStatus(200);

    $lead->refresh();
    expect($lead->qualification_status)->toBe('qualified');

    $qualification = LeadQualification::where('lead_id', $lead->id)->latest()->first();
    expect($qualification)->not->toBeNull()
        ->and($qualification->status)->toBe('qualified');
});

it('disqualifies a lead with a reason', function () {
    $this->loginAsAdmin();

    $pipeline = Pipeline::first() ?? Pipeline::create(['name' => 'Default']);
    $stage = Stage::first() ?? Stage::create(['name' => 'New', 'code' => 'new', 'lead_pipeline_id' => $pipeline->id]);

    $lead = app(LeadRepository::class)->create([
        'title' => 'Test Lead',
        'lead_pipeline_id' => $pipeline->id,
        'lead_pipeline_stage_id' => $stage->id,
        'qualification_status' => 'in_review',
    ]);

    $response = $this->postJson(route('admin.leads.disqualify', $lead->id), [
        'reason' => 'Budget mismatch',
    ]);

    $response->assertStatus(200);

    $lead->refresh();
    expect($lead->qualification_status)->toBe('disqualified');

    $qualification = LeadQualification::where('lead_id', $lead->id)->latest()->first();
    expect($qualification)->not->toBeNull()
        ->and($qualification->reason)->toBe('Budget mismatch');
});
