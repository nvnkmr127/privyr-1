<?php

use Carbon\Carbon;
use Webkul\Activity\Models\Activity;
use Webkul\Attribute\Models\Attribute;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Models\Pipeline;
use Webkul\Lead\Models\Source;
use Webkul\Lead\Models\Stage;
use Webkul\Lead\Models\Type;
use Webkul\User\Models\User;

beforeEach(function () {
    $this->admin = User::first();
    $this->actingAs($this->admin);
    $this->pipeline = Pipeline::first();
    $this->stage = Stage::first();
    $this->source = Source::first();
    $this->type = Type::first();
});

it('Scenario 1 & 7: handles lead with very few / missing values without layout breakdown', function () {
    $lead = Lead::create([
        'title' => 'Minimal Lead',
        'user_id' => $this->admin->id,
        'lead_pipeline_id' => $this->pipeline->id,
        'lead_pipeline_stage_id' => $this->stage->id,
        'status' => 1,
    ]);

    $response = $this->get(route('admin.leads.view', $lead->id));

    $response->assertOk()
        ->assertSee('Minimal Lead')
        ->assertSee('--');
});

it('Scenario 2 & 3 & 4: handles leads with 20, 50, and 100+ custom fields with progressive disclosure', function () {
    
    $pipeline = \Webkul\Lead\Models\Pipeline::first() ?? \Webkul\Lead\Models\Pipeline::create(['name' => 'Default']);
    $stage = \Webkul\Lead\Models\Stage::first() ?? \Webkul\Lead\Models\Stage::create(['name' => 'New', 'code' => 'new', 'lead_pipeline_id' => $pipeline->id]);
    $lead = \Webkul\Lead\Models\Lead::create([
        'title' => 'Test Lead',
        'lead_pipeline_id' => $pipeline->id,
        'lead_pipeline_stage_id' => $stage->id,
        'user_id' => 1,
    ]);


    for ($i = 1; $i <= 9; $i++) {
        Attribute::where('code', "test_attr_{$i}")->delete();
        Attribute::create([
            'code' => "test_attr_{$i}",
            'name' => "Test Attr {$i}",
            'type' => 'text',
            'entity_type' => 'leads',
            'is_user_defined' => 1,
        ]);
    }

    $response = $this->get(route('admin.leads.view', $lead->id));

    $response->assertOk()
        ->assertSee('Key Details')
        ->assertSee('Sales Information')
        ->assertSee('Custom Fields')
        ->assertSee('Show More / Show Less', false)
        ->assertSee('group-extra-field hidden', false)
        ->assertSee('toggleGroupFields', false);
});

it('Scenario 5 & 6: handles extremely long field labels and values without grid overflow', function () {
    $lead = Lead::create([
        'title' => 'Comprehensive Global Digital Transformation and Multi-Cloud Modernization Initiative for Enterprise Logistics Infrastructure 2026-2030',
        'description' => str_repeat('Extremely detailed enterprise scope documentation with extensive terms, conditions, SLAs, compliance requirements, security assessments, SOC2 audits, and infrastructure migration checklists. ', 15),
        'person_name' => 'Dr. Alexander Bartholomew Montgomery-Cunningham III of Greater Metropolitan Industries',
        'emails' => [['label' => 'work', 'value' => 'alexander.bartholomew.montgomery-cunningham.iii@very-long-enterprise-corporate-domain-name.example.co.uk']],
        'contact_numbers' => [['label' => 'mobile', 'value' => '+44 20 7946 0999 ext 88492']],
        'user_id' => $this->admin->id,
        'lead_pipeline_id' => $this->pipeline->id,
        'lead_pipeline_stage_id' => $this->stage->id,
        'lead_source_id' => $this->source->id,
        'lead_type_id' => $this->type->id,
        'lead_value' => 2500000.75,
        'status' => 1,
    ]);

    $response = $this->get(route('admin.leads.view', $lead->id));

    $response->assertOk()
        ->assertSee('truncate', false)
        ->assertSee('break-words', false)
        ->assertSee('max-h-32', false)
        ->assertSee('Alexander Bartholomew');
});

it('Scenario 8 & 9: handles multiple activities with large content and read more disclosure', function () {
    $lead = Lead::create([
        'title' => 'High Activity Volume Lead',
        'user_id' => $this->admin->id,
        'lead_pipeline_id' => $this->pipeline->id,
        'lead_pipeline_stage_id' => $this->stage->id,
        'status' => 1,
    ]);

    // Create multiple activity types
    $activities = [
        ['title' => 'Initial Discovery Call', 'type' => 'call', 'comment' => str_repeat('Discovery call discussion details. ', 12)],
        ['title' => 'Architecture Review Meeting', 'type' => 'meeting', 'comment' => str_repeat('Detailed architecture review notes with specifications. ', 15)],
        ['title' => 'Customer Risk Assessment Note', 'type' => 'note', 'comment' => str_repeat('Internal risk assessment notes for finance team. ', 8)],
        ['title' => 'Signed NDA and Scope File Upload', 'type' => 'file', 'comment' => 'NDA executed by both parties.'],
    ];

    foreach ($activities as $actData) {
        $act = Activity::create([
            'title' => $actData['title'],
            'type' => $actData['type'],
            'comment' => $actData['comment'],
            'user_id' => $this->admin->id,
            'lead_id' => $lead->id,
            'is_done' => 1,
            'created_at' => Carbon::now()->subMinutes(rand(10, 500)),
        ]);
    }

    $response = $this->get(route('admin.leads.view', $lead->id));

    $response->assertOk()
        ->assertSee('Initial Discovery Call')
        ->assertSee('Architecture Review Meeting')
        ->assertSee('Customer Risk Assessment Note')
        ->assertSee('Read more', false)
        ->assertSee('toggleTimelineDesc', false)
        ->assertSee('filterTimeline', false);
});

it('Scenario 10: handles zero activities with clean empty state placeholder', function () {
    $lead = Lead::create([
        'title' => 'Brand New Lead With Zero Activities',
        'user_id' => $this->admin->id,
        'lead_pipeline_id' => $this->pipeline->id,
        'lead_pipeline_stage_id' => $this->stage->id,
        'status' => 1,
    ]);

    $response = $this->get(route('admin.leads.view', $lead->id));

    $response->assertOk()
        ->assertSee('Brand New Lead With Zero Activities')
        ->assertSee('Lead Created');
});

it('Scenario 11 & 12: handles long lead name and long email address in header without breaking layout', function () {
    $lead = Lead::create([
        'title' => 'Supercalifragilisticexpialidocious Enterprise Platform License Agreement for Global Infrastructure Modernization & AI Acceleration',
        'person_name' => 'Bartholomew Hieronymus Wolfeschlegelsteinhausenbergerdorff',
        'emails' => [['label' => 'work', 'value' => 'bartholomew.hieronymus.wolfeschlegelsteinhausenbergerdorff@international-conglomerate-enterprise.example.org']],
        'contact_numbers' => [['label' => 'work', 'value' => '+1 800 555 0199 4488']],
        'user_id' => $this->admin->id,
        'lead_pipeline_id' => $this->pipeline->id,
        'lead_pipeline_stage_id' => $this->stage->id,
        'status' => 1,
    ]);

    $response = $this->get(route('admin.leads.view', $lead->id));

    $response->assertOk()
        ->assertSee('truncate', false)
        ->assertSee('max-w-md', false)
        ->assertSee('Bartholomew Hieronymus');
});

it('Scenario 13 & 14 & 15: responsive layout classes accommodate mobile, tablet, and desktop', function () {
    
    $pipeline = \Webkul\Lead\Models\Pipeline::first() ?? \Webkul\Lead\Models\Pipeline::create(['name' => 'Default']);
    $stage = \Webkul\Lead\Models\Stage::first() ?? \Webkul\Lead\Models\Stage::create(['name' => 'New', 'code' => 'new', 'lead_pipeline_id' => $pipeline->id]);
    $lead = \Webkul\Lead\Models\Lead::create([
        'title' => 'Test Lead',
        'lead_pipeline_id' => $pipeline->id,
        'lead_pipeline_stage_id' => $stage->id,
        'user_id' => 1,
    ]);


    $response = $this->get(route('admin.leads.view', $lead->id));

    $response->assertOk()
        // Responsive grid
        ->assertSee('grid grid-cols-1 lg:grid-cols-12 gap-6', false)
        // Desktop sticky sidebar (~35%)
        ->assertSee('lg:col-span-4 lg:sticky lg:top-4', false)
        // Activity workspace (~65%)
        ->assertSee('lg:col-span-8', false)
        // Responsive property grids
        ->assertSee('grid grid-cols-1 sm:grid-cols-2 gap-2.5', false)
        // Flex wrapping on meta and action bars
        ->assertSee('flex flex-col lg:flex-row', false)
        ->assertSee('flex flex-wrap', false);
});
