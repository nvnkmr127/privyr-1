<?php

use Webkul\Lead\Models\Lead;
use Webkul\User\Models\User;
use Webkul\Lead\Models\Pipeline;
use Webkul\Lead\Models\Stage;
use Webkul\User\Models\Group;

uses(\Tests\TestCase::class);

beforeEach(function () {
    $this->user = User::factory()->create(['role_id' => 1]); // Admin
    $this->actingAs($this->user, 'user');
    
    $this->pipeline = Pipeline::factory()->create();
    $this->stage1 = Stage::factory()->create(['lead_pipeline_id' => $this->pipeline->id]);
    $this->stage2 = Stage::factory()->create(['lead_pipeline_id' => $this->pipeline->id]);

    $this->leads = Lead::factory()->count(5)->create([
        'user_id' => $this->user->id,
        'lead_pipeline_id' => $this->pipeline->id,
        'lead_pipeline_stage_id' => $this->stage1->id,
        'status' => 'Open',
        'priority' => 'low',
        'temperature' => 'Cold'
    ]);
});

it('can bulk change status', function () {
    $indices = $this->leads->pluck('id')->toArray();

    $response = $this->postJson(route('admin.leads.bulk'), [
        'action' => 'change_status',
        'indices' => $indices,
        'value' => 'Working',
        'mode' => 'partial'
    ]);

    $response->assertStatus(200);
    $response->assertJsonPath('data.successful_count', 5);

    foreach ($this->leads as $lead) {
        expect($lead->fresh()->status)->toBe('Working');
    }
});

it('can bulk change stage', function () {
    $indices = $this->leads->pluck('id')->toArray();

    $response = $this->postJson(route('admin.leads.bulk'), [
        'action' => 'change_stage',
        'indices' => $indices,
        'value' => (string) $this->stage2->id,
        'mode' => 'partial'
    ]);

    $response->assertStatus(200);

    foreach ($this->leads as $lead) {
        expect($lead->fresh()->lead_pipeline_stage_id)->toBe($this->stage2->id);
    }
});

it('can bulk change priority', function () {
    $indices = $this->leads->pluck('id')->toArray();

    $response = $this->postJson(route('admin.leads.bulk'), [
        'action' => 'change_priority',
        'indices' => $indices,
        'value' => 'urgent',
        'mode' => 'partial'
    ]);

    $response->assertStatus(200);

    foreach ($this->leads as $lead) {
        expect($lead->fresh()->priority)->toBe('urgent');
    }
});

it('prevents bulk update on unauthorized leads', function () {
    // Create a regular user and some leads owned by another user
    $otherUser = User::factory()->create(['role_id' => 2]); // Not admin
    $otherLead = Lead::factory()->create(['user_id' => $otherUser->id]);
    
    // We act as a user with limited view
    $limitedUser = User::factory()->create(['role_id' => 2]);
    $this->actingAs($limitedUser, 'user');

    $response = $this->postJson(route('admin.leads.bulk'), [
        'action' => 'change_priority',
        'indices' => [$otherLead->id],
        'value' => 'urgent',
        'mode' => 'partial'
    ]);

    $response->assertStatus(200); // 200 because it's a partial failure response
    $response->assertJsonPath('data.failed_count', 1);
    $response->assertJsonPath('data.successful_count', 0);
    
    expect($otherLead->fresh()->priority)->not->toBe('urgent');
});
