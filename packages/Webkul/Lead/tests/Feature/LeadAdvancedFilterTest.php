<?php

use Illuminate\Support\Facades\DB;
use Webkul\Attribute\Models\Attribute;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Services\LeadFilterService;
use Webkul\User\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    // Setup a user
    $this->user = User::factory()->create();
    actingAs($this->user, 'user');
});

it('can filter leads by normalized phone number', function () {
    // Create a lead with a phone number
    $lead = Lead::factory()->create([
        'phones' => json_encode([['value' => '+1 (555) 123-4567']]),
    ]);

    $service = app(LeadFilterService::class);
    $query = Lead::query();

    // Test global search
    $service->applyAdvancedFilters($query, ['all' => ['5551234567']], 'all', []);

    expect($query->get())->toHaveCount(1);
    expect($query->first()->id)->toBe($lead->id);
});

it('can apply EAV filters correctly using EXISTS without joining', function () {
    $attribute = Attribute::factory()->create([
        'entity_type' => 'leads',
        'code' => 'custom_property',
        'type' => 'text',
    ]);

    $lead = Lead::factory()->create();

    DB::table('attribute_values')->insert([
        'entity_id' => $lead->id,
        'entity_type' => 'leads',
        'attribute_id' => $attribute->id,
        'text_value' => 'Apartment',
    ]);

    $service = app(LeadFilterService::class);
    $query = Lead::query();

    // The filter structure from datagrid is usually ['column_name' => ['value']]
    $service->applyAdvancedFilters($query, ['custom_property' => ['Apartment']], 'all', []);

    $results = $query->get();
    expect($results)->toHaveCount(1);
    expect($results->first()->id)->toBe($lead->id);

    // Check SQL doesn't use JOIN (to ensure performance/no duplicates)
    $sql = $query->toSql();
    expect($sql)->not->toContain('inner join `attribute_values`');
    expect($sql)->toContain('exists (select 1 from `attribute_values`');
});

it('can use ANY match type to combine filters with OR', function () {
    $lead1 = Lead::factory()->create(['title' => 'Test A']);
    $lead2 = Lead::factory()->create(['title' => 'Test B']);

    $service = app(LeadFilterService::class);
    $query = Lead::query();

    // Match type = 'any' (OR logic)
    $service->applyAdvancedFilters($query, [
        'title' => ['operator' => 'equals', 'value' => 'Test A'],
        'id' => ['operator' => 'equals', 'value' => $lead2->id],
    ], 'any', []);

    $results = $query->get();
    expect($results)->toHaveCount(2);
});
