<?php

use Webkul\Lead\Repositories\LeadRepository;
use Webkul\Lead\Models\Lead;
use Webkul\Activity\Models\Activity;
use Webkul\Lead\Services\LeadFollowUpService;
use Webkul\User\Models\User;
use Illuminate\Support\Facades\Event;
use Carbon\Carbon;

uses(Tests\TestCase::class);

beforeEach(function () {
    Event::fake([
        'lead.follow_up.created',
        'lead.follow_up.completed',
        'lead.follow_up.rescheduled',
        'lead.no_next_action',
    ]);
});

function createTestLead() {
    return app(LeadRepository::class)->create([
        'entity_type' => 'leads',
        'title' => 'Test Lead',
        'status' => 'New',
        'source_id' => 1,
        'type_id' => 1,
        'user_id' => 1,
        'lead_pipeline_id' => 1,
        'lead_pipeline_stage_id' => 1,
        'expected_close_date' => Carbon::now()->addDays(10),
    ]);
}

it('calculates next action and syncs correctly', function () {
    $lead = createTestLead();
    $service = app(LeadFollowUpService::class);
    
    // Create follow up
    $activity = $service->schedule($lead, 'call', Carbon::now()->addDays(2)->toDateTimeString());
    
    $lead->refresh();
    
    expect($lead->next_action)->toBe('call');
    expect($lead->next_follow_up_at)->not->toBeNull();
    
    Event::assertDispatched('lead.follow_up.created');
});

it('reschedules follow up and dispatches event', function () {
    $lead = createTestLead();
    $service = app(LeadFollowUpService::class);
    $activity = $service->schedule($lead, 'meeting', Carbon::now()->addDays(1)->toDateTimeString());
    
    $service->snooze($lead, Carbon::now()->addDays(3)->toDateTimeString());
    
    $lead->refresh();
    
    expect($lead->next_follow_up_at->isFuture())->toBeTrue();
    Event::assertDispatched('lead.follow_up.rescheduled');
});

it('completes follow up and clears next action', function () {
    $lead = createTestLead();
    $service = app(LeadFollowUpService::class);
    $activity = $service->schedule($lead, 'email', Carbon::now()->addDays(1)->toDateTimeString());
    
    $service->complete($lead, 'Done');
    
    $lead->refresh();
    
    expect($lead->next_action)->toBeNull();
    expect($lead->next_follow_up_at)->toBeNull();
    
    Event::assertDispatched('lead.follow_up.completed');
});
