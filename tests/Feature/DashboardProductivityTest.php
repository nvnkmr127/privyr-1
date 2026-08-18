<?php

use Webkul\Lead\Models\Lead;
use Webkul\Admin\Helpers\LeadProductivityDashboard;
use Carbon\Carbon;
use Webkul\User\Models\User;

it('calculates productivity metrics correctly', function () {
    $this->loginAsAdmin();
    $user = User::orderBy('id')->first();
    $repo = app(\Webkul\Lead\Repositories\LeadRepository::class);

    // 1. New Lead (created today)
    $repo->create(['title' => 'New', 'entity_type' => 'leads', 'lead_pipeline_id' => 1, 'lead_pipeline_stage_id' => 1, 'created_at' => Carbon::today()]);

    // 2. Unassigned
    $repo->create(['title' => 'Unassigned', 'entity_type' => 'leads', 'lead_pipeline_id' => 1, 'lead_pipeline_stage_id' => 1, 'user_id' => null]);

    // 3. Needs contact
    $repo->create(['title' => 'Needs Contact', 'entity_type' => 'leads', 'lead_pipeline_id' => 1, 'lead_pipeline_stage_id' => 1, 'is_unread' => true, 'last_contacted_at' => null]);

    // 4. Follow-up Due Today
    $repo->create(['title' => 'Due', 'entity_type' => 'leads', 'lead_pipeline_id' => 1, 'lead_pipeline_stage_id' => 1, 'next_follow_up_at' => Carbon::today(), 'follow_up_owner_id' => $user->id, 'next_action' => 'Call']);

    // 5. Overdue Follow-up
    $repo->create(['title' => 'Overdue', 'entity_type' => 'leads', 'lead_pipeline_id' => 1, 'lead_pipeline_stage_id' => 1, 'next_follow_up_at' => Carbon::yesterday(), 'follow_up_owner_id' => $user->id, 'next_action' => 'Email']);

    // 6. Hot Lead
    $repo->create(['title' => 'Hot', 'entity_type' => 'leads', 'lead_pipeline_id' => 1, 'lead_pipeline_stage_id' => 1, 'priority' => 'urgent']);

    // 7. Stale Lead
    $repo->create(['title' => 'Stale', 'entity_type' => 'leads', 'lead_pipeline_id' => 1, 'lead_pipeline_stage_id' => 1, 'last_contacted_at' => Carbon::now()->subDays(20)]);

    // 8. No next action
    $repo->create(['title' => 'No Action', 'entity_type' => 'leads', 'lead_pipeline_id' => 1, 'lead_pipeline_stage_id' => 1, 'next_action' => null, 'next_follow_up_at' => null]);

    // 9. Qualified
    $repo->create(['title' => 'Qualified', 'entity_type' => 'leads', 'lead_pipeline_id' => 1, 'lead_pipeline_stage_id' => 1, 'qualification_status' => 'qualified']);

    // 10. Waiting for response
    $repo->create(['title' => 'Waiting', 'entity_type' => 'leads', 'lead_pipeline_id' => 1, 'lead_pipeline_stage_id' => 1, 'is_unread' => false, 'last_contacted_at' => Carbon::now()]);

    $dashboard = app(LeadProductivityDashboard::class);
    $metrics = $dashboard->getMetrics();

    expect($metrics['new_leads'])->toBeGreaterThanOrEqual(1);
    expect($metrics['unassigned'])->toBeGreaterThanOrEqual(1);
    expect($metrics['needs_contact'])->toBeGreaterThanOrEqual(1);
    expect($metrics['due_today'])->toBeGreaterThanOrEqual(1);
    expect($metrics['overdue'])->toBeGreaterThanOrEqual(1);
    expect($metrics['hot'])->toBeGreaterThanOrEqual(1);
    expect($metrics['stale'])->toBeGreaterThanOrEqual(1);
    expect($metrics['no_next_action'])->toBeGreaterThanOrEqual(1);
    expect($metrics['qualified'])->toBeGreaterThanOrEqual(1);
    expect($metrics['waiting_for_response'])->toBeGreaterThanOrEqual(1);

    // Test next actions
    $actions = $dashboard->getMyNextActions();
    expect($actions->count())->toBeGreaterThanOrEqual(2); // Overdue + Due Today
});
