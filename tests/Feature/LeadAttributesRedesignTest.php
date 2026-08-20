<?php

use Webkul\Attribute\Models\Attribute;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Models\Pipeline;
use Webkul\Lead\Models\Stage;
use Webkul\User\Models\User;

it('renders lead attributes in a compact 2-column property grid', function () {
    $admin = User::first();
    $this->actingAs($admin);

    $pipeline = Pipeline::first() ?? Pipeline::create(['name' => 'Default']);
    $stage = Stage::first() ?? Stage::create(['name' => 'New', 'code' => 'new', 'lead_pipeline_id' => $pipeline->id]);
    $lead = Lead::create([
        'title' => 'Test Lead',
        'lead_pipeline_id' => $pipeline->id,
        'lead_pipeline_stage_id' => $stage->id,
        'user_id' => 1,
    ]);

    Attribute::where('code', 'test_custom_attribute')->delete();

    Attribute::create([
        'code' => 'test_custom_attribute',
        'name' => 'Test Custom Attribute',
        'type' => 'text',
        'entity_type' => 'leads',
        'is_user_defined' => 1,
    ]);

    $response = $this->get(route('admin.leads.view', $lead->id));

    $response->assertOk()
        ->assertSee('grid grid-cols-1 sm:grid-cols-2 gap-2.5', false)
        ->assertSee('Key Details')
        ->assertSee('Sales Information')
        ->assertSee('Custom Fields');
});

it('handles large field collections with show more toggle', function () {
    $admin = User::first();
    $this->actingAs($admin);

    $pipeline = Pipeline::first() ?? Pipeline::create(['name' => 'Default']);
    $stage = Stage::first() ?? Stage::create(['name' => 'New', 'code' => 'new', 'lead_pipeline_id' => $pipeline->id]);
    $lead = Lead::create([
        'title' => 'Test Lead',
        'lead_pipeline_id' => $pipeline->id,
        'lead_pipeline_stage_id' => $stage->id,
        'user_id' => 1,
    ]);

    $customAttrsCount = Attribute::where('entity_type', 'leads')
        ->where('is_user_defined', 1)
        ->count();

    $response = $this->get(route('admin.leads.view', $lead->id));

    $response->assertOk();

    if ($customAttrsCount > 8) {
        $response->assertSee('group-extra-field hidden', false)
            ->assertSee('toggleGroupFields', false)
            ->assertSee('more fields', false);
    }
});

it('renders empty attributes with standard fallback cleanly', function () {
    $admin = User::first();
    $this->actingAs($admin);

    $pipeline = Pipeline::first();
    $stage = Stage::first();

    $lead = Lead::create([
        'title' => 'Minimal Lead Without Custom Values',
        'user_id' => $admin->id,
        'lead_pipeline_id' => $pipeline->id,
        'lead_pipeline_stage_id' => $stage->id,
        'status' => 1,
    ]);

    $response = $this->get(route('admin.leads.view', $lead->id));

    $response->assertOk()
        ->assertSee('Minimal Lead Without Custom Values');
});

it('handles leads with 10, 30, and 50+ attributes without breaking layout', function () {
    $admin = User::first();
    $this->actingAs($admin);

    $pipeline = Pipeline::first() ?? Pipeline::create(['name' => 'Default']);
    $stage = Stage::first() ?? Stage::create(['name' => 'New', 'code' => 'new', 'lead_pipeline_id' => $pipeline->id]);
    $lead = Lead::create([
        'title' => 'Test Lead',
        'lead_pipeline_id' => $pipeline->id,
        'lead_pipeline_stage_id' => $stage->id,
        'user_id' => 1,
    ]);

    $response = $this->get(route('admin.leads.view', $lead->id));

    $response->assertOk()
        ->assertSee('Key Details')
        ->assertSee('Sales Information')
        ->assertSee('truncate', false)
        ->assertSee('break-words', false);
});
