<?php

use Webkul\Activity\Models\Activity;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Models\Pipeline;
use Webkul\Lead\Models\Source;
use Webkul\Lead\Models\Stage;
use Webkul\Lead\Models\Type;
use Webkul\User\Models\User;

beforeEach(function () {
    $this->admin = User::first();
    $this->actingAs($this->admin);

    $this->pipeline = Pipeline::first() ?? Pipeline::create(['name' => 'Default']);
    $this->stage = Stage::first() ?? Stage::create(['name' => 'New', 'code' => 'new', 'lead_pipeline_id' => $this->pipeline->id]);
    $this->source = Source::first() ?? Source::create(['name' => 'Direct']);
    $this->type = Type::first() ?? Type::create(['name' => 'Demo']);

    $this->person_name = 'Test Person';
    $this->emails = [['value' => 'test@example.com', 'label' => 'work']];
    $this->contact_numbers = [['value' => '1234567890', 'label' => 'work']];
});

test('it duplicates a lead', function () {
    $lead = Lead::create([
        'title' => 'Original Lead',
        'lead_value' => 500,
        'user_id' => $this->admin->id,
        'person_name' => $this->person_name,
        'emails' => $this->emails,
        'contact_numbers' => $this->contact_numbers,
        'lead_source_id' => $this->source->id,
        'lead_type_id' => $this->type->id,
        'lead_pipeline_id' => $this->pipeline->id,
        'lead_pipeline_stage_id' => $this->stage->id,
    ]);

    $this->get(route('admin.leads.duplicate', $lead->id))
        ->assertRedirect();

    $this->assertDatabaseHas('leads', [
        'title' => 'Clone of Original Lead',
        'lead_value' => 500,
    ]);
});

test('it merges two leads correctly', function () {
    $lead1 = Lead::create([
        'title' => 'Primary Lead',
        'lead_value' => 0,
        'user_id' => $this->admin->id,
        'person_name' => $this->person_name,
        'emails' => $this->emails,
        'contact_numbers' => $this->contact_numbers,
        'lead_source_id' => $this->source->id,
        'lead_type_id' => $this->type->id,
        'lead_pipeline_id' => $this->pipeline->id,
        'lead_pipeline_stage_id' => $this->stage->id,
    ]);

    $lead2 = Lead::create([
        'title' => 'Target Lead',
        'lead_value' => 750,
        'description' => 'Target Description',
        'user_id' => $this->admin->id,
        'person_name' => $this->person_name,
        'emails' => $this->emails,
        'contact_numbers' => $this->contact_numbers,
        'lead_source_id' => $this->source->id,
        'lead_type_id' => $this->type->id,
        'lead_pipeline_id' => $this->pipeline->id,
        'lead_pipeline_stage_id' => $this->stage->id,
    ]);

    $activity = Activity::create([
        'type' => 'note',
        'comment' => 'Target Note',
        'lead_id' => $lead2->id,
    ]);

    $this->post(route('admin.leads.merge.store', $lead1->id), [
        'target_lead_id' => $lead2->id,
    ])->assertRedirect();

    $this->assertDatabaseMissing('leads', ['id' => $lead2->id]);

    $lead1->refresh();
    expect((float) $lead1->lead_value)->toBe(750.0);
    expect($lead1->description)->toBe('Target Description');
});

test('it automatically calculates lead score', function () {
    $lead = Lead::create([
        'title' => 'Scored Lead',
        'lead_value' => 100,
        'user_id' => $this->admin->id,
        'person_name' => $this->person_name,
        'emails' => $this->emails,
        'contact_numbers' => $this->contact_numbers,
        'lead_source_id' => $this->source->id,
        'lead_type_id' => $this->type->id,
        'lead_pipeline_id' => $this->pipeline->id,
        'lead_pipeline_stage_id' => $this->stage->id,
        'priority' => 'urgent',
    ]);

    expect($lead->lead_score)->toBeGreaterThan(0);
});
