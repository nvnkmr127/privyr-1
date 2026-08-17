<?php

use Webkul\Lead\Models\Lead;
use Webkul\Lead\Models\Pipeline;
use Webkul\Lead\Models\Stage;

it('renders the lead inbox page for authenticated admin', function () {
    $this->loginAsAdmin();

    $response = $this->get(route('admin.leads.inbox'));

    $response->assertOk()
        ->assertViewIs('admin::leads.inbox');
});

it('returns json lead list for inbox data request', function () {
    $this->loginAsAdmin();

    $response = $this->get(route('admin.leads.inbox.data', ['preset' => 'all']));

    $response->assertOk()
        ->assertJsonStructure([
            'data',
            'meta' => [
                'current_page',
                'last_page',
                'per_page',
                'total',
            ],
        ]);
});

it('handles swipe actions on a lead', function () {
    $this->loginAsAdmin();

    $pipeline = Pipeline::first();
    $stage = Stage::first();

    $lead = Lead::create([
        'title' => 'Swipe Lead',
        'lead_pipeline_id' => $pipeline->id,
        'lead_pipeline_stage_id' => $stage->id,
        'status' => 1,
        'is_unread' => true,
        'is_archived' => false,
    ]);

    $response = $this->postJson(route('admin.leads.inbox.swipe'), [
        'lead_id' => $lead->id,
        'action' => 'mark_read',
    ]);

    $response->assertOk()
        ->assertJsonPath('message', trans('admin::app.leads.inbox.action-success'));

    expect($lead->fresh()->is_unread)->toBeFalse();
});

it('handles bulk action on multiple leads', function () {
    $this->loginAsAdmin();

    $pipeline = Pipeline::first();
    $stage = Stage::first();

    $leads = collect([
        Lead::create([
            'title' => 'Bulk Lead 1',
            'lead_pipeline_id' => $pipeline->id,
            'lead_pipeline_stage_id' => $stage->id,
            'status' => 1,
            'is_archived' => false,
        ]),
        Lead::create([
            'title' => 'Bulk Lead 2',
            'lead_pipeline_id' => $pipeline->id,
            'lead_pipeline_stage_id' => $stage->id,
            'status' => 1,
            'is_archived' => false,
        ]),
    ]);

    $response = $this->postJson(route('admin.leads.inbox.bulk'), [
        'lead_ids' => $leads->pluck('id')->toArray(),
        'action' => 'archive',
    ]);

    $response->assertOk()
        ->assertJsonPath('message', trans('admin::app.leads.inbox.bulk-success'));

    foreach ($leads as $lead) {
        expect($lead->fresh()->is_archived)->toBeTrue();
    }
});
