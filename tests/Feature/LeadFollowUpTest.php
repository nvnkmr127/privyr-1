<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Webkul\Activity\Models\Activity;
use Webkul\Lead\Models\Lead;
use Webkul\User\Models\User;

beforeEach(function () {
    // Clear tests DB state if needed
});

it('computes lead follow-up states dynamically', function () {
    $this->loginAsAdmin();
    $admin = User::orderBy('id')->first();

    $lead = app(\Webkul\Lead\Repositories\LeadRepository::class)->create([
        'title' => 'State Test Lead',
        'entity_type' => 'leads',
        'lead_pipeline_id' => 1,
        'lead_pipeline_stage_id' => 1,
        'user_id' => $admin->id,
        'is_unread' => true,
    ]);

    expect($lead->getFollowUpStateAttribute())->toBe('Needs Attention');

    $lead->update(['is_unread' => false, 'last_contacted_at' => Carbon::now()]);
    $lead->refresh();
    expect($lead->getFollowUpStateAttribute())->toBe('No Next Action');

    $lead->update(['last_contacted_at' => Carbon::now()->subDays(15)]);
    $lead->refresh();
    expect($lead->getFollowUpStateAttribute())->toBe('Stale');

    $lead->update(['next_follow_up_at' => Carbon::tomorrow()]);
    $lead->refresh();
    expect($lead->getFollowUpStateAttribute())->toBe('Upcoming');

    $lead->update(['next_follow_up_at' => Carbon::today()]);
    $lead->refresh();
    expect($lead->getFollowUpStateAttribute())->toBe('Due Today');

    $lead->update(['next_follow_up_at' => Carbon::yesterday()]);
    $lead->refresh();
    expect($lead->getFollowUpStateAttribute())->toBe('Overdue');
});

it('schedules snoozes and completes a follow-up', function () {
    $this->loginAsAdmin();
    $admin = User::orderBy('id')->first();

    $lead = app(\Webkul\Lead\Repositories\LeadRepository::class)->create([
        'title' => 'Follow-up Test Lead',
        'entity_type' => 'leads',
        'lead_pipeline_id' => 1,
        'lead_pipeline_stage_id' => 1,
        'user_id' => $admin->id,
    ]);

    $service = app(\Webkul\Lead\Services\LeadFollowUpService::class);

    // Schedule
    $date = Carbon::tomorrow()->toDateTimeString();
    $service->schedule($lead, 'Call back', $date, $admin->id);

    $lead->refresh();
    expect($lead->next_action)->toBe('Call back')
        ->and($lead->next_follow_up_at->toDateTimeString())->toBe($date)
        ->and($lead->follow_up_owner_id)->toBe($admin->id);

    // Snooze
    $newDate = Carbon::tomorrow()->addDay()->toDateTimeString();
    $service->snooze($lead, $newDate);

    $lead->refresh();
    expect($lead->next_follow_up_at->toDateTimeString())->toBe($newDate);

    // Complete
    $service->complete($lead, 'Client answered and is interested');

    $lead->refresh();
    expect($lead->next_action)->toBeNull()
        ->and($lead->next_follow_up_at)->toBeNull()
        ->and($lead->follow_up_owner_id)->toBeNull()
        ->and($lead->last_contacted_at)->not->toBeNull()
        ->and($lead->getFollowUpStateAttribute())->toBe('No Next Action');

    // Verify activity logged
    $activity = Activity::where('lead_id', $lead->id)
        ->where('type', 'system')
        ->where('title', 'like', 'Completed Follow-up%')
        ->latest('id')
        ->first();
    expect($activity)->not->toBeNull()
        ->and($activity->title)->toContain('Completed Follow-up: Call back')
        ->and($activity->comment)->toBe('Client answered and is interested');
});
