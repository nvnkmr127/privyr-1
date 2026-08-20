<?php

use Tests\TestCase;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Models\LeadAudit;
use Webkul\User\Models\User;

uses(TestCase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('audits lead creation', function () {
    $lead = Lead::factory()->create([
        'title' => 'Test Lead',
    ]);

    $audit = LeadAudit::where('lead_id', $lead->id)
        ->where('action', 'created')
        ->first();

    expect($audit)->not->toBeNull();
    expect($audit->source)->toBe('Manual');
    expect($audit->user_id)->toBe($this->user->id);
});

it('audits lead updates for trackable fields', function () {
    $lead = Lead::factory()->create([
        'status' => 'New',
    ]);

    $lead->update([
        'status' => 'Qualified',
    ]);

    $audit = LeadAudit::where('lead_id', $lead->id)
        ->where('action', 'updated')
        ->where('field', 'status')
        ->first();

    expect($audit)->not->toBeNull();
    expect($audit->old_value)->toBe('New');
    expect($audit->new_value)->toBe('Qualified');
});

it('prevents audit records from being updated', function () {
    $lead = Lead::factory()->create();
    $audit = LeadAudit::where('lead_id', $lead->id)->first();

    $this->expectException(Exception::class);
    $this->expectExceptionMessage('Audit records cannot be modified.');

    $audit->update(['source' => 'Hacked']);
});

it('prevents audit records from being deleted', function () {
    $lead = Lead::factory()->create();
    $audit = LeadAudit::where('lead_id', $lead->id)->first();

    $this->expectException(Exception::class);
    $this->expectExceptionMessage('Audit records cannot be deleted.');

    $audit->delete();
});

it('can fetch audit history via api if authorized', function () {
    // Assuming 'view_lead_audit' permission is granted to this user (role)
    // Or we mock the bouncer. For simplicity, we just check route presence and mock bouncer if needed.
    bouncer()->allow($this->user)->to('leads.view_audit');

    $lead = Lead::factory()->create();

    $response = $this->getJson(route('admin.leads.audits.index', $lead->id));

    $response->assertStatus(200);
    $response->assertJsonStructure(['data', 'meta']);
});

it('forbids fetching audit history if unauthorized', function () {
    bouncer()->disallow($this->user)->to('leads.view_audit');

    $lead = Lead::factory()->create();

    $response = $this->getJson(route('admin.leads.audits.index', $lead->id));

    $response->assertStatus(403);
});
