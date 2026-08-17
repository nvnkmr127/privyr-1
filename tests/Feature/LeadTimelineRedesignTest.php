<?php

use Webkul\Activity\Models\Activity;
use Webkul\Lead\Models\Lead;
use Webkul\User\Models\User;

it('renders the activity timeline with responsive two-column proportions and sticky sidebar', function () {
    $admin = User::first();
    $this->actingAs($admin);

    $lead = Lead::first();

    $response = $this->get(route('admin.leads.view', $lead->id));

    $response->assertOk()
        ->assertSee('lg:col-span-4 lg:sticky lg:top-4', false)
        ->assertSee('lg:col-span-8 flex flex-col', false)
        ->assertSee('Chronological Activity Timeline')
        ->assertSee('timeline-filters', false);
});

it('renders chronological timeline events with category filter buttons and badges', function () {
    $admin = User::first();
    $this->actingAs($admin);

    $lead = Lead::first();

    // Attach a call activity with long description
    $activity = Activity::create([
        'title' => 'Project Kickoff Call with Client',
        'type' => 'call',
        'comment' => str_repeat('Detailed discussion regarding requirements and milestones. ', 10),
        'user_id' => $admin->id,
        'is_done' => 1,
    ]);

    $lead->activities()->attach($activity->id);

    $response = $this->get(route('admin.leads.view', $lead->id));

    $response->assertOk()
        ->assertSee('Project Kickoff Call with Client')
        ->assertSee('filterTimeline', false)
        ->assertSee('toggleTimelineDesc', false)
        ->assertSee('Read more', false);
});
