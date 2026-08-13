<?php

use Webkul\Lead\Models\Lead;

it('renders the lead profile page with master timeline', function () {
    $this->loginAsAdmin();

    $lead = Lead::factory()->create([
        'title' => 'Test Profile Lead',
        'lead_score' => 85,
        'utm_source' => 'facebook',
        'utm_medium' => 'cpc',
        'utm_campaign' => 'summer_sale',
    ]);

    $response = $this->get(route('admin.leads.view', $lead->id));

    $response->assertOk()
        ->assertSee('Test Profile Lead')
        ->assertSee('Chronological Activity Timeline');
});

it('returns formatted chronological timeline events for a lead', function () {
    $lead = Lead::factory()->create([
        'title' => 'Timeline Lead',
    ]);

    $timeline = $lead->getChronologicalTimeline();

    expect($timeline)->not->toBeEmpty();
    expect($timeline->first()['type'])->toBe('lead_created');
});
