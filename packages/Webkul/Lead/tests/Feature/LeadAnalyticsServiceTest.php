<?php

use Webkul\Lead\Models\Lead;
use Webkul\Lead\Services\Analytics\LeadCoreAnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can get core metrics', function () {
    // Arrange
    $service = app(LeadCoreAnalyticsService::class);
    
    // Act
    $startDate = now()->subDays(30)->toDateTimeString();
    $endDate = now()->toDateTimeString();
    
    $metrics = $service->getMetrics($startDate, $endDate);
    
    // Assert
    expect($metrics)->toBeArray()
        ->and($metrics)->toHaveKeys([
            'total_leads', 'new_leads', 'open_leads', 'working_leads',
            'qualified_leads', 'unqualified_leads', 'nurturing_leads',
            'won_leads', 'lost_leads', 'junk_leads', 'unassigned_leads',
            'inactive_leads', 'needs_attention_leads', 'overdue_followups',
            'conversion_rate'
        ]);
        
    expect($metrics['total_leads'])->toBe(0);
});
