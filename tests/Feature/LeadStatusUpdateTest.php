<?php

use Webkul\Lead\Models\Lead;
use Webkul\Lead\Models\Pipeline;
use Webkul\Lead\Models\Stage;
use Webkul\Lead\Services\LeadLifecycleService;

function createLead($admin)
{
    $pipeline = Pipeline::withoutGlobalScopes()->first() ?? Pipeline::withoutGlobalScopes()->create(['name' => 'Default']);
    $stage = Stage::first() ?? Stage::create(['name' => 'New', 'code' => 'new', 'lead_pipeline_id' => $pipeline->id]);

    return Lead::create([
        'title' => 'Test Lead',
        'lead_pipeline_id' => $pipeline->id,
        'lead_pipeline_stage_id' => $stage->id,
        'user_id' => $admin->id,
        'status' => 1,
    ]);
}

it('can update lead status via admin endpoint', function () {
    $admin = $this->loginAsAdmin();
    $lead = createLead($admin);

    $response = $this->putJson(route('admin.leads.status.update', $lead->id), [
        'status' => 'Working',
        'reason' => 'Completed work',
    ]);

    $response->assertStatus(200);
    $response->assertJsonFragment(['message' => trans('admin::app.leads.update-success')]);
});

it('returns 400 on failure during status update', function () {
    $admin = $this->loginAsAdmin();
    $lead = createLead($admin);

    $this->mock(LeadLifecycleService::class, function ($mock) {
        $mock->shouldReceive('changeStatus')->andThrow(new Exception('Failed to update'));
    });

    $response = $this->putJson(route('admin.leads.status.update', $lead->id), [
        'status' => 'Won',
    ]);

    $response->assertStatus(400);
    $response->assertJsonFragment(['message' => 'Failed to update']);
});
