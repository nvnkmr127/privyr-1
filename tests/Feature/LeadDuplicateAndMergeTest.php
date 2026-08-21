<?php

use Webkul\Activity\Models\Activity;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Models\LeadMergeHistory;
use Webkul\Lead\Services\LeadDuplicateService;
use Webkul\Lead\Services\LeadMergeService;

beforeEach(function () {
    $this->duplicateService = app(LeadDuplicateService::class);
    $this->mergeService = app(LeadMergeService::class);
});

it('normalizes phone numbers correctly', function () {
    // US format without country code (not assumed India due to heuristics matching only 10 digits exactly)
    // Wait, 10 digits without country code will be assumed India as per our logic!
    // So '555-123-4567' -> '5551234567' -> '+915551234567'
    expect($this->duplicateService->normalizePhone('+1 (555) 123-4567'))->toBe('+15551234567');
    expect($this->duplicateService->normalizePhone('555-123-4567'))->toBe('+915551234567'); // India-first for 10 digits

    // India-first local
    expect($this->duplicateService->normalizePhone('09876543210'))->toBe('+919876543210');
    expect($this->duplicateService->normalizePhone('9876543210'))->toBe('+919876543210');

    // 00 for international
    expect($this->duplicateService->normalizePhone('0015551234567'))->toBe('+15551234567');

    // Explicit 91 without plus
    expect($this->duplicateService->normalizePhone('919876543210'))->toBe('+919876543210');

    // Keep existing valid plus numbers
    expect($this->duplicateService->normalizePhone('  +91 98765 43210 '))->toBe('+919876543210');
    expect($this->duplicateService->normalizePhone(''))->toBeNull();
});

it('normalizes emails correctly', function () {
    expect($this->duplicateService->normalizeEmail(' Test@Example.com '))->toBe('test@example.com');
    expect($this->duplicateService->normalizeEmail(''))->toBeNull();
});

it('detects exact phone duplicates', function () {
    $existing = Lead::factory()->create([
        'normalized_primary_phone' => '+15551234567',
        'is_merged' => false,
    ]);

    $incoming = [
        'person' => ['contact_numbers' => '+1 (555) 123-4567'],
    ];

    $result = $this->duplicateService->detect($incoming, 'manual');

    expect($result)->not->toBeNull();
    expect($result['existing_lead_id'])->toBe($existing->id);
    expect($result['match_type'])->toBe('exact_phone');
    expect($result['confidence'])->toBe('high');
});

it('detects exact email duplicates', function () {
    $existing = Lead::factory()->create([
        'normalized_primary_email' => 'test@example.com',
        'is_merged' => false,
    ]);

    $incoming = [
        'person' => ['emails' => 'Test@Example.com'],
    ];

    $result = $this->duplicateService->detect($incoming, 'manual');

    expect($result)->not->toBeNull();
    expect($result['existing_lead_id'])->toBe($existing->id);
    expect($result['match_type'])->toBe('exact_email');
    expect($result['confidence'])->toBe('high');
});

it('detects external id matches', function () {
    $existing = Lead::factory()->create([
        'origin' => 'meta',
        'external_id' => '12345',
        'is_merged' => false,
    ]);

    $incoming = [
        'person' => ['name' => 'John Doe'],
    ];

    $result = $this->duplicateService->detect($incoming, 'meta', '12345');

    expect($result)->not->toBeNull();
    expect($result['existing_lead_id'])->toBe($existing->id);
    expect($result['match_type'])->toBe('external_id');
});

it('ignores name-only matches', function () {
    Lead::factory()->create([
        'person_name' => 'John Doe',
        'is_merged' => false,
    ]);

    $incoming = [
        'person' => ['name' => 'John Doe'],
    ];

    $result = $this->duplicateService->detect($incoming, 'manual');

    expect($result)->toBeNull();
});

it('merges leads correctly and transfers activities', function () {
    $surviving = Lead::factory()->create([
        'lead_value' => 0,
        'description' => null,
    ]);

    $merged = Lead::factory()->create([
        'lead_value' => 500,
        'description' => 'Test description',
    ]);

    // Assign an activity to the merged lead
    $activity = Activity::factory()->create([
        'lead_id' => $merged->id,
    ]);

    $this->mergeService->merge($surviving->id, $merged->id, [
        'lead_value' => $merged->lead_value,
        'description' => $merged->description,
    ], 1, 'Test merge');

    $surviving->refresh();
    $merged->refresh();

    // Verify fields updated
    expect((float) $surviving->lead_value)->toBe(500.0);
    expect($surviving->description)->toBe('Test description');

    // Verify relations moved
    expect($activity->fresh()->lead_id)->toBe($surviving->id);

    // Verify soft deletion (merge flags)
    expect($merged->is_merged)->toBeTrue();
    expect($merged->merged_into_id)->toBe($surviving->id);
    expect($merged->duplicate_status)->toBe('confirmed_duplicate');

    // Verify history recorded
    $history = LeadMergeHistory::first();
    expect($history->surviving_lead_id)->toBe($surviving->id);
    expect($history->merged_lead_id)->toBe($merged->id);
});

it('prevents invalid merges', function () {
    $surviving = Lead::factory()->create();

    expect(fn () => $this->mergeService->merge($surviving->id, $surviving->id, [], 1))
        ->toThrow(Exception::class, 'A lead cannot be merged into itself.');
});
