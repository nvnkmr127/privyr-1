<?php

namespace Webkul\Lead\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Models\LeadQualificationProxy;
use Webkul\Lead\Services\LeadQualificationService;
use Webkul\User\Models\User;

class LeadQualificationTest extends TestCase
{
    use RefreshDatabase;

    protected $qualificationService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->qualificationService = app(LeadQualificationService::class);
        
        // Setup config for tests
        config(['lead.qualification.required_attributes' => ['budget', 'timeline']]);
    }

    /** @test */
    public function it_identifies_missing_fields_for_qualification()
    {
        $lead = Lead::factory()->create([
            'budget' => null,
            'timeline' => 'Immediate',
        ]);

        $missing = $this->qualificationService->getMissingFields($lead);
        
        $this->assertContains('budget', $missing);
        $this->assertNotContains('timeline', $missing);
        $this->assertFalse($this->qualificationService->canBeQualified($lead));
    }

    /** @test */
    public function it_qualifies_a_lead_when_requirements_are_met()
    {
        Event::fake([
            'lead.qualification.started',
            'lead.qualification.updated',
            'lead.qualification.qualified'
        ]);

        $lead = Lead::factory()->create([
            'budget' => '1000',
            'timeline' => 'Immediate',
            'qualification_status' => 'in_review',
        ]);

        $this->assertTrue($this->qualificationService->canBeQualified($lead));

        $qualifiedLead = $this->qualificationService->qualify($lead);

        $this->assertEquals('qualified', $qualifiedLead->qualification_status);
        
        $this->assertDatabaseHas('lead_qualifications', [
            'lead_id' => $lead->id,
            'status' => 'qualified',
        ]);

        Event::assertDispatched('lead.qualification.qualified');
        Event::assertDispatched('lead.qualification.updated');
    }

    /** @test */
    public function it_throws_exception_if_qualifying_with_missing_fields()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot qualify lead. Missing required fields: budget');

        $lead = Lead::factory()->create([
            'budget' => null,
            'timeline' => 'Immediate',
            'qualification_status' => 'in_review',
        ]);

        $this->qualificationService->qualify($lead);
    }

    /** @test */
    public function it_disqualifies_a_lead_with_a_reason()
    {
        Event::fake([
            'lead.qualification.updated',
            'lead.qualification.disqualified'
        ]);

        $lead = Lead::factory()->create([
            'qualification_status' => 'in_review',
        ]);

        $disqualifiedLead = $this->qualificationService->disqualify($lead, 'Budget mismatch');

        $this->assertEquals('disqualified', $disqualifiedLead->qualification_status);
        
        $this->assertDatabaseHas('lead_qualifications', [
            'lead_id' => $lead->id,
            'status' => 'disqualified',
            'reason' => 'Budget mismatch',
        ]);

        Event::assertDispatched('lead.qualification.disqualified');
    }

    /** @test */
    public function it_requalifies_a_disqualified_lead()
    {
        $lead = Lead::factory()->create([
            'qualification_status' => 'disqualified',
        ]);

        LeadQualificationProxy::modelClass()::create([
            'lead_id' => $lead->id,
            'status' => 'disqualified',
            'reason' => 'Too early',
        ]);

        $requalifiedLead = $this->qualificationService->requalify($lead);

        $this->assertEquals('in_review', $requalifiedLead->qualification_status);
        
        $this->assertDatabaseHas('lead_qualifications', [
            'lead_id' => $lead->id,
            'status' => 'in_review',
        ]);
    }
}
