<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Webkul\Activity\Models\Activity;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Models\LeadNurtureEnrollment;
use Webkul\Lead\Models\LeadNurtureSequence;
use Webkul\Lead\Models\LeadNurtureStep;
use Webkul\Lead\Repositories\LeadRepository;
use Webkul\Lead\Services\LeadNurtureEngine;
use Webkul\User\Models\User;

beforeEach(function () {
    //
});

it('processes a nurture sequence correctly including waits and conditions', function () {
    $this->loginAsAdmin();
    $admin = User::orderBy('id')->first();

    $lead = app(LeadRepository::class)->create([
        'title' => 'Nurture Test Lead',
        'entity_type' => 'leads',
        'lead_pipeline_id' => 1,
        'lead_pipeline_stage_id' => 1,
        'user_id' => $admin->id,
        'is_unread' => true,
    ]);

    // Create sequence
    $sequence = LeadNurtureSequence::create([
        'name' => 'Welcome Sequence',
        'stop_condition' => ['type' => 'replied'],
    ]);

    // Step 1: Message
    $step1 = LeadNurtureStep::create([
        'sequence_id' => $sequence->id,
        'type' => 'message',
        'config' => ['channel' => 'email', 'content' => 'Welcome to our service!'],
    ]);

    // Step 2: Wait
    $step2 = LeadNurtureStep::create([
        'sequence_id' => $sequence->id,
        'type' => 'wait',
        'config' => ['days' => 1],
    ]);

    // Step 3: Condition (engaged)
    $step3 = LeadNurtureStep::create([
        'sequence_id' => $sequence->id,
        'type' => 'condition',
        'config' => ['type' => 'engaged'],
    ]);

    // Step 4a: Message (engaged branch)
    $step4a = LeadNurtureStep::create([
        'sequence_id' => $sequence->id,
        'type' => 'message',
        'config' => ['channel' => 'whatsapp', 'content' => 'Glad you replied!'],
    ]);

    // Step 4b: Message (not engaged branch)
    $step4b = LeadNurtureStep::create([
        'sequence_id' => $sequence->id,
        'type' => 'message',
        'config' => ['channel' => 'email', 'content' => 'Still there?'],
    ]);

    // Link steps
    $step1->update(['next_step_id' => $step2->id]);
    $step2->update(['next_step_id' => $step3->id]);
    $step3->update(['next_step_id' => $step4a->id, 'alt_next_step_id' => $step4b->id]);

    // Enroll Lead
    $enrollment = LeadNurtureEnrollment::create([
        'lead_id' => $lead->id,
        'sequence_id' => $sequence->id,
        'current_step_id' => $step1->id,
        'status' => 'active',
    ]);

    $engine = app(LeadNurtureEngine::class);

    // Initial Processing - Should run step 1, move to step 2, and pause at step 2 wait.
    $engine->processActiveEnrollments();

    $enrollment->refresh();
    expect($enrollment->current_step_id)->toBe($step2->id)
        ->and($enrollment->resume_at)->not->toBeNull()
        ->and($enrollment->status)->toBe('active');

    // Verify Step 1 message logged
    $activity = Activity::where('lead_id', $lead->id)->where('type', 'system')->latest('id')->first();
    expect($activity->comment)->toBe('Welcome to our service!');

    // Process again immediately - should NOT advance because of wait
    $engine->processActiveEnrollments();
    $enrollment->refresh();
    expect($enrollment->current_step_id)->toBe($step2->id);

    // Fast forward time to pass the wait
    Carbon::setTestNow(Carbon::now()->addDays(2));

    // Process again - should clear wait, evaluate condition.
    // Lead is unread (is_unread = true) -> not engaged -> takes alt branch -> Step 4b -> End
    $engine->processActiveEnrollments();

    $enrollment->refresh();
    // It should have executed step 3 (condition), taken 4b (not engaged), executed 4b, and completed
    expect($enrollment->status)->toBe('completed')
        ->and($enrollment->current_step_id)->toBeNull();

    // Verify 4b message logged
    $activity = Activity::where('lead_id', $lead->id)->where('type', 'system')->latest('id')->first();
    expect($activity->comment)->toBe('Still there?');

    // Reset time
    Carbon::setTestNow();
});

it('stops sequence if stop condition is met', function () {
    $this->loginAsAdmin();
    $admin = User::orderBy('id')->first();

    $lead = app(LeadRepository::class)->create([
        'title' => 'Nurture Test Lead 2',
        'entity_type' => 'leads',
        'lead_pipeline_id' => 1,
        'lead_pipeline_stage_id' => 1,
        'user_id' => $admin->id,
        'is_unread' => false, // implies replied
    ]);

    $sequence = LeadNurtureSequence::create([
        'name' => 'Stop Condition Sequence',
        'stop_condition' => ['type' => 'replied'],
    ]);

    $step1 = LeadNurtureStep::create([
        'sequence_id' => $sequence->id,
        'type' => 'message',
        'config' => ['channel' => 'email', 'content' => 'Hello'],
    ]);

    $enrollment = LeadNurtureEnrollment::create([
        'lead_id' => $lead->id,
        'sequence_id' => $sequence->id,
        'current_step_id' => $step1->id,
        'status' => 'active',
    ]);

    $engine = app(LeadNurtureEngine::class);

    $engine->processActiveEnrollments();

    $enrollment->refresh();
    expect($enrollment->status)->toBe('stopped');
});
