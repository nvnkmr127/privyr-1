<?php

use Webkul\Activity\Models\Activity;
use Webkul\Lead\Models\Lead;
use Webkul\User\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

beforeEach(function () {
    $this->user = User::first() ?? User::create([
        'name' => 'Admin',
        'email' => 'admin_test_'.uniqid().'@example.com',
        'password' => bcrypt('password'),
        'role_id' => 1,
        'view_permission' => 'global',
        'status' => 1,
    ]);

    $this->lead = Lead::first() ?? Lead::create([
        'title' => 'Test Lead',
        'user_id' => $this->user->id,
    ]);
});

it('can create a follow-up', function () {
    actingAs($this->user);

    $response = postJson(route('admin.follow_ups.store'), [
        'type' => 'call',
        'lead_id' => $this->lead->id,
        'title' => 'Initial Call',
        'schedule_from' => now()->addDays(1)->toDateTimeString(),
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('activities', [
        'title' => 'Initial Call',
        'type' => 'call',
        'lead_id' => $this->lead->id,
        'status' => 'pending',
    ]);
});

it('can complete a follow-up', function () {
    actingAs($this->user);
    $activity = Activity::create([
        'title' => 'Test Activity',
        'type' => 'call',
        'lead_id' => $this->lead->id,
        'status' => 'pending',
        'schedule_from' => now(),
    ]);

    $response = postJson(route('admin.follow_ups.complete', $activity->id));

    $response->assertStatus(200);
    $this->assertDatabaseHas('activities', [
        'id' => $activity->id,
        'status' => 'completed',
        'is_done' => 1,
    ]);
});

it('cannot complete an already completed follow-up', function () {
    actingAs($this->user);
    $activity = Activity::create([
        'title' => 'Test Activity 2',
        'type' => 'call',
        'lead_id' => $this->lead->id,
        'status' => 'completed',
        'schedule_from' => now(),
    ]);

    $response = postJson(route('admin.follow_ups.complete', $activity->id));
    $response->assertStatus(400);
});

it('can detect overdue follow-ups', function () {
    $overdueActivity = Activity::create([
        'title' => 'Overdue Activity',
        'type' => 'call',
        'lead_id' => $this->lead->id,
        'status' => 'pending',
        'schedule_from' => now()->subDays(1),
    ]);

    $upcomingActivity = Activity::create([
        'title' => 'Upcoming Activity',
        'type' => 'call',
        'lead_id' => $this->lead->id,
        'status' => 'pending',
        'schedule_from' => now()->addDays(1),
    ]);

    $overdueCount = Activity::overdue()->where('id', $overdueActivity->id)->count();
    $upcomingCount = Activity::overdue()->where('id', $upcomingActivity->id)->count();

    $this->assertEquals(1, $overdueCount);
    $this->assertEquals(0, $upcomingCount);
});
