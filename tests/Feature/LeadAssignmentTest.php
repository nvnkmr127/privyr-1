<?php

use Webkul\Activity\Models\Activity;
use Webkul\Lead\Models\LeadAssignment;
use Webkul\Lead\Models\LeadAssignmentRule;
use Webkul\Lead\Models\LeadAssignmentRuleCondition;
use Webkul\Lead\Repositories\LeadRepository;
use Webkul\User\Models\User;

beforeEach(function () {
    LeadAssignmentRule::query()->delete();
});

it('logs manual assignment and updates timeline', function () {
    $this->loginAsAdmin();
    $user1 = User::create(['name' => 'User 1', 'email' => uniqid().'@example.com', 'password' => bcrypt('password'), 'role_id' => 1]);
    $user2 = User::create(['name' => 'User 2', 'email' => uniqid().'@example.com', 'password' => bcrypt('password'), 'role_id' => 1]);

    $lead = app(LeadRepository::class)->create([
        'title' => 'Manual Lead',
        'entity_type' => 'leads',
        'lead_pipeline_id' => 1,
        'lead_pipeline_stage_id' => 1,
        'user_id' => $user1->id,
    ]);

    // Initial manual assignment
    expect($lead->user_id)->toBe($user1->id);

    $assignment = LeadAssignment::where('lead_id', $lead->id)->latest('id')->first();
    expect($assignment)->not->toBeNull()
        ->and($assignment->assigned_to)->toBe($user1->id)
        ->and($assignment->reason)->toBe('Manual Assignment');

    // Timeline Activity
    $activity = Activity::where('lead_id', $lead->id)->where('type', 'system')->latest('id')->first();
    expect($activity)->not->toBeNull()
        ->and($activity->title)->toContain('assigned to');

    // Reassignment
    $lead = app(LeadRepository::class)->update(['entity_type' => 'leads', 'user_id' => $user2->id], $lead->id);

    $assignment2 = LeadAssignment::where('lead_id', $lead->id)->latest('id')->first();
    expect($assignment2)->not->toBeNull()
        ->and($assignment2->assigned_to)->toBe($user2->id)
        ->and($assignment2->previous_owner)->toBe($user1->id)
        ->and($assignment2->reason)->toBe('Manual Reassignment');
});

it('executes direct rule assignment based on conditions', function () {
    $this->loginAsAdmin();
    $user1 = User::create(['name' => 'User 1', 'email' => uniqid().'@example.com', 'password' => bcrypt('password'), 'role_id' => 1]);

    $rule = LeadAssignmentRule::create([
        'name' => 'Direct FB Rule',
        'type' => 'direct',
        'status' => true,
        'sort_order' => 1,
    ]);

    LeadAssignmentRuleCondition::create([
        'rule_id' => $rule->id,
        'attribute' => 'utm_source',
        'operator' => 'equals',
        'value' => 'facebook',
    ]);

    $rule->users()->attach($user1->id);

    $lead = app(LeadRepository::class)->create([
        'title' => 'FB Lead',
        'entity_type' => 'leads',
        'lead_pipeline_id' => 1,
        'lead_pipeline_stage_id' => 1,
        'utm_source' => 'facebook',
    ]);

    expect($lead->user_id)->toBe($user1->id);

    $assignment = LeadAssignment::where('lead_id', $lead->id)->latest('id')->first();
    expect($assignment)->not->toBeNull()
        ->and($assignment->assigned_to)->toBe($user1->id)
        ->and($assignment->reason)->toContain('Assigned via Rule');
});

it('executes round robin assignment', function () {
    $this->loginAsAdmin();
    $user1 = User::create(['name' => 'User 1', 'email' => uniqid().'@example.com', 'password' => bcrypt('password'), 'role_id' => 1]);
    $user2 = User::create(['name' => 'User 2', 'email' => uniqid().'@example.com', 'password' => bcrypt('password'), 'role_id' => 1]);

    $rule = LeadAssignmentRule::create([
        'name' => 'Round Robin Rule',
        'type' => 'round_robin',
        'status' => true,
        'sort_order' => 1,
    ]);

    // user1 was assigned a long time ago, user2 recently
    $rule->users()->attach($user1->id, ['last_assigned_at' => now()->subDays(2)]);
    $rule->users()->attach($user2->id, ['last_assigned_at' => now()->subDays(1)]);

    $lead1 = app(LeadRepository::class)->create([
        'title' => 'RR Lead 1',
        'entity_type' => 'leads',
        'lead_pipeline_id' => 1,
        'lead_pipeline_stage_id' => 1,
    ]);

    // Should assign to user1 because their last_assigned_at is older
    expect($lead1->user_id)->toBe($user1->id);

    $lead2 = app(LeadRepository::class)->create([
        'title' => 'RR Lead 2',
        'entity_type' => 'leads',
        'lead_pipeline_id' => 1,
        'lead_pipeline_stage_id' => 1,
    ]);

    // Now user1 has a recent timestamp, so user2 should be next
    expect($lead2->user_id)->toBe($user2->id);
});
it('executes least assigned assignment', function () {
    $this->loginAsAdmin();
    $user1 = User::create(['name' => 'User 1', 'email' => uniqid().'@example.com', 'password' => bcrypt('password'), 'role_id' => 1]);
    $user2 = User::create(['name' => 'User 2', 'email' => uniqid().'@example.com', 'password' => bcrypt('password'), 'role_id' => 1]);

    $rule = LeadAssignmentRule::create([
        'name' => 'Least Assigned Rule',
        'type' => 'least_assigned',
        'status' => true,
        'sort_order' => 1,
    ]);

    $rule->users()->attach([$user1->id, $user2->id]);

    // Give user1 a lead
    app(LeadRepository::class)->create([
        'title' => 'Existing Lead',
        'entity_type' => 'leads',
        'lead_pipeline_id' => 1,
        'lead_pipeline_stage_id' => 1,
        'user_id' => $user1->id,
    ]);

    // user2 has 0 leads, user1 has 1. Rule should pick user2.
    $lead1 = app(LeadRepository::class)->create([
        'title' => 'New Lead',
        'entity_type' => 'leads',
        'lead_pipeline_id' => 1,
        'lead_pipeline_stage_id' => 1,
    ]);

    expect($lead1->user_id)->toBe($user2->id);
});

it('executes capacity based assignment with fallback', function () {
    $this->loginAsAdmin();
    $user1 = User::create(['name' => 'User 1', 'email' => uniqid().'@example.com', 'password' => bcrypt('password'), 'role_id' => 1]);
    $user2 = User::create(['name' => 'User 2', 'email' => uniqid().'@example.com', 'password' => bcrypt('password'), 'role_id' => 1]);
    $fallbackUser = User::create(['name' => 'Fallback User', 'email' => uniqid().'@example.com', 'password' => bcrypt('password'), 'role_id' => 1]);

    $rule = LeadAssignmentRule::create([
        'name' => 'Capacity Rule',
        'type' => 'capacity_based',
        'status' => true,
        'sort_order' => 1,
        'fallback_type' => 'user',
        'fallback_user_id' => $fallbackUser->id,
    ]);

    // Capacity 1 for user1
    $rule->users()->attach($user1->id, ['capacity' => 1, 'last_assigned_at' => now()->subDays(2)]);
    // Capacity 0 for user2 (should skip)
    $rule->users()->attach($user2->id, ['capacity' => 0, 'last_assigned_at' => now()->subDays(1)]);

    // This should go to user1 since they have capacity 1 and 0 leads
    $lead1 = app(LeadRepository::class)->create([
        'title' => 'Capacity Lead 1',
        'entity_type' => 'leads',
        'lead_pipeline_id' => 1,
        'lead_pipeline_stage_id' => 1,
    ]);

    expect($lead1->user_id)->toBe($user1->id);

    // This should hit fallback because user1 capacity is full, and user2 capacity is 0
    $lead2 = app(LeadRepository::class)->create([
        'title' => 'Capacity Lead 2',
        'entity_type' => 'leads',
        'lead_pipeline_id' => 1,
        'lead_pipeline_stage_id' => 1,
    ]);

    expect($lead2->user_id)->toBe($fallbackUser->id);
});
