<?php

use Webkul\Lead\Models\Lead;
use Webkul\Activity\Models\Activity;
use Webkul\User\Models\User;
use Carbon\Carbon;

use function Pest\Laravel\get;
use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->admin = User::factory()->create(['role_id' => 1]); // Admin
    $this->user = User::factory()->create(['role_id' => 2]); // Sales Rep
});

it('loads dashboard for authenticated user', function () {
    actingAs($this->user, 'user')
        ->get(route('admin.dashboard.index'))
        ->assertOk()
        ->assertSee('Lead Action Center');
});

it('calculates today metrics correctly for new leads', function () {
    Lead::factory()->count(3)->create(['created_at' => Carbon::today()]);
    Lead::factory()->count(2)->create(['created_at' => Carbon::yesterday()]);

    actingAs($this->admin, 'user')
        ->get(route('admin.dashboard.index'))
        ->assertOk()
        ->assertViewHas('metrics', function ($metrics) {
            return $metrics['today']['new_leads'] === 3;
        });
});

it('calculates health metrics correctly for overdue leads', function () {
    Lead::factory()->create(['next_follow_up_at' => Carbon::now()->subDays(2)]);
    Lead::factory()->create(['next_follow_up_at' => Carbon::now()->addDays(2)]);

    actingAs($this->admin, 'user')
        ->get(route('admin.dashboard.index'))
        ->assertOk()
        ->assertViewHas('metrics', function ($metrics) {
            return $metrics['health']['overdue'] === 1;
        });
});

it('applies user_id filter from request', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    Lead::factory()->count(2)->create(['user_id' => $user1->id, 'created_at' => Carbon::today()]);
    Lead::factory()->count(3)->create(['user_id' => $user2->id, 'created_at' => Carbon::today()]);

    actingAs($this->admin, 'user')
        ->get(route('admin.dashboard.index', ['user_id' => $user1->id]))
        ->assertOk()
        ->assertViewHas('metrics', function ($metrics) {
            return $metrics['today']['new_leads'] === 2;
        });
});
