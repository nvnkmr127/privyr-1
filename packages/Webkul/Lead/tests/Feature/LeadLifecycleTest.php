<?php

namespace Webkul\Lead\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use Webkul\Activity\Models\Activity;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Models\LeadStatusHistoryProxy;
use Webkul\Lead\Models\Pipeline;
use Webkul\Lead\Models\Stage;
use Webkul\Lead\Services\LeadLifecycleService;
use Webkul\User\Models\Role;
use Webkul\User\Models\User;

class LeadLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected $lifecycleService;

    protected $pipelineId;

    protected $stageId;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->lifecycleService = app(LeadLifecycleService::class);

        $role = Role::create([
            'name' => 'Administrator',
            'description' => 'Admin Role',
            'permission_type' => 'all',
        ]);

        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role_id' => $role->id,
        ]);
        $this->actingAs($this->user, 'user');

        $pipeline = Pipeline::create([
            'name' => 'Default Pipeline',
            'is_default' => 1,
        ]);
        $this->pipelineId = $pipeline->id;

        $stage = Stage::create([
            'code' => 'new',
            'name' => 'New',
            'lead_pipeline_id' => $this->pipelineId,
        ]);
        $this->stageId = $stage->id;
    }

    /** @test */
    public function it_can_convert_a_lead()
    {
        Event::fake([
            'lead.status.changed',
            'lead.status.converted',
        ]);

        $lead = Lead::create([
            'title' => 'Test Lead',
            'status' => 'Working',
            'lead_pipeline_id' => $this->pipelineId,
            'lead_pipeline_stage_id' => $this->stageId,
        ]);

        $this->lifecycleService->convertLead($lead, [
            'conversion_reason' => 'Great product',
            'user_id' => $this->user->id,
        ]);

        $lead->refresh();

        $this->assertEquals('Converted', $lead->status);
        $this->assertNotNull($lead->converted_at);
        $this->assertEquals($this->user->id, $lead->converted_by);

        // Verify history was created
        $history = LeadStatusHistoryProxy::where('lead_id', $lead->id)->latest()->first();
        $this->assertEquals('Working', $history->previous_status);
        $this->assertEquals('Converted', $history->new_status);

        Event::assertDispatched('lead.status.converted');
    }

    /** @test */
    public function it_can_mark_a_lead_as_lost_and_cancel_follow_ups()
    {
        $lead = Lead::create([
            'title' => 'Test Lead',
            'status' => 'Working',
            'lead_pipeline_id' => $this->pipelineId,
            'lead_pipeline_stage_id' => $this->stageId,
        ]);

        // Create a pending follow-up
        $activity = Activity::create([
            'title' => 'Test activity',
            'lead_id' => $lead->id,
            'type' => 'call',
            'status' => 'pending',
            'schedule_from' => now()->addDays(2),
            'schedule_to' => now()->addDays(2)->addMinutes(30),
            'is_done' => 0,
        ]);

        $this->lifecycleService->markLost($lead, [
            'lost_reason' => 'Too expensive',
            'follow_up_action' => 'cancel',
        ]);

        $lead->refresh();

        $this->assertEquals('Lost', $lead->status);
        $this->assertEquals('Too expensive', $lead->lost_reason);

        $activity->refresh();
        $this->assertEquals('completed', $activity->status);
        $this->assertEquals(1, $activity->is_done);
    }

    /** @test */
    public function it_can_mark_a_lead_as_junk()
    {
        $lead = Lead::create([
            'title' => 'Test Lead',
            'status' => 'Working',
            'lead_pipeline_id' => $this->pipelineId,
            'lead_pipeline_stage_id' => $this->stageId,
        ]);

        $this->lifecycleService->markJunk($lead, [
            'junk_reason' => 'Spam',
        ]);

        $lead->refresh();

        $this->assertEquals('Junk', $lead->status);
        $this->assertEquals('Spam', $lead->junk_reason);
    }

    /** @test */
    public function it_can_reopen_a_lost_lead()
    {
        $lead = Lead::create([
            'title' => 'Test Lead',
            'status' => 'Lost',
            'lost_reason' => 'Too expensive',
            'lead_pipeline_id' => $this->pipelineId,
            'lead_pipeline_stage_id' => $this->stageId,
        ]);

        $this->lifecycleService->reopenLead($lead, 'Client came back', $this->user->id);

        $lead->refresh();

        $this->assertEquals('Working', $lead->status);
        $this->assertNull($lead->lost_reason);
    }

    /** @test */
    public function it_cannot_convert_an_already_converted_lead()
    {
        $this->expectException(\Exception::class);

        $lead = Lead::create([
            'title' => 'Test Lead',
            'status' => 'Converted',
            'lead_pipeline_id' => $this->pipelineId,
            'lead_pipeline_stage_id' => $this->stageId,
        ]);

        $this->lifecycleService->convertLead($lead);
    }
}
