<?php

use Webkul\Activity\Models\Activity;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Models\LeadQualification;

it('updates qualification status and logs activity', function () {
    $this->loginAsAdmin();

    $lead = Lead::create([
        'title' => 'Test Lead',
        'lead_pipeline_id' => 1,
        'lead_pipeline_stage_id' => 1,
        'qualification_status' => null,
    ]);

    $response = $this->putJson(route('admin.leads.attributes.update', $lead->id), [
        'qualification_status' => 'qualified',
        'qualification_reason' => 'Met all BANT criteria',
    ]);

    $response->assertStatus(200);

    $lead->refresh();
    expect($lead->qualification_status)->toBe('qualified');

    $qualification = LeadQualification::where('lead_id', $lead->id)->latest()->first();
    expect($qualification)->not->toBeNull()
        ->and($qualification->status)->toBe('qualified')
        ->and($qualification->reason)->toBe('Met all BANT criteria');

    $activity = Activity::where('lead_id', $lead->id)->where('type', 'system')->latest('id')->first();
    expect($activity)->not->toBeNull()
        ->and($activity->title)->toContain('Qualified')
        ->and($activity->comment)->toBe('Met all BANT criteria');
});
