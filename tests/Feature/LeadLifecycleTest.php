<?php

use Illuminate\Support\Facades\Event;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Services\LeadLifecycleService;

beforeEach(function () {
    $this->lifecycleService = app(LeadLifecycleService::class);
});

it('can change lead status to Working and create history', function () {
    $lead = Lead::factory()->create(['status' => 'Open']);

    Event::fake();

    $this->lifecycleService->changeStatus($lead, 'Working', 'Started working');

    expect($lead->fresh()->status)->toBe('Working');

    $this->assertDatabaseHas('lead_status_histories', [
        'lead_id' => $lead->id,
        'previous_status' => 'Open',
        'new_status' => 'Working',
        'reason' => 'Started working',
    ]);

    Event::assertDispatched('lead.status.updated');
});

it('can convert a lead', function () {
    $lead = Lead::factory()->create(['status' => 'Open']);
    
    $user = \Webkul\User\Models\User::factory()->create();
    $this->actingAs($user);

    $this->lifecycleService->changeStatus($lead, 'Converted');

    $freshLead = $lead->fresh();
    expect($freshLead->status)->toBe('Converted')
        ->and($freshLead->converted_at)->not->toBeNull()
        ->and($freshLead->converted_by)->toBe($user->id);
});

it('can mark a lead as lost with a reason', function () {
    $lead = Lead::factory()->create(['status' => 'Open']);

    $this->lifecycleService->changeStatus($lead, 'Lost', 'Not interested');

    $freshLead = $lead->fresh();
    expect($freshLead->status)->toBe('Lost')
        ->and($freshLead->lost_reason)->toBe('Not interested');
});

it('can reopen a lost lead', function () {
    $lead = Lead::factory()->create([
        'status' => 'Lost',
        'lost_reason' => 'Not interested'
    ]);

    $this->lifecycleService->reopenLead($lead, 'Client called back');

    $freshLead = $lead->fresh();
    expect($freshLead->status)->toBe('Open')
        ->and($freshLead->lost_reason)->toBeNull();

    $this->assertDatabaseHas('lead_status_histories', [
        'lead_id' => $lead->id,
        'previous_status' => 'Lost',
        'new_status' => 'Open',
        'reason' => 'Client called back',
    ]);
});
