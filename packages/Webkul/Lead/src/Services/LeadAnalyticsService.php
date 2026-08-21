<?php

namespace Webkul\Lead\Services;

use Webkul\Lead\Services\Analytics\LeadCoreAnalyticsService;
use Webkul\Lead\Services\Analytics\LeadOwnerAnalyticsService;
use Webkul\Lead\Services\Analytics\LeadPipelineAnalyticsService;
use Webkul\Lead\Services\Analytics\LeadSourceAnalyticsService;
use Webkul\Lead\Services\Analytics\LeadTrendAnalyticsService;

class LeadAnalyticsService
{
    public function __construct(
        protected LeadCoreAnalyticsService $coreAnalytics,
        protected LeadSourceAnalyticsService $sourceAnalytics,
        protected LeadOwnerAnalyticsService $ownerAnalytics,
        protected LeadPipelineAnalyticsService $pipelineAnalytics,
        protected LeadTrendAnalyticsService $trendAnalytics
    ) {}

    /**
     * Get aggregate analytics for a given date range and optional user.
     * Facade method combining the specialized analytics services.
     */
    public function getMetrics(string $startDate, string $endDate, ?int $userId = null): array
    {
        $coreMetrics = $this->coreAnalytics->getMetrics($startDate, $endDate, $userId);

        return [
            'total_leads' => $coreMetrics['total_leads'],
            'qualified_leads' => $coreMetrics['qualified_leads'],
            'unqualified_leads' => $coreMetrics['unqualified_leads'],
            'won_leads' => $coreMetrics['won_leads'],
            'lost_leads' => $coreMetrics['lost_leads'],
            'conversion_rate' => $coreMetrics['conversion_rate'],
            'overdue_followups' => $coreMetrics['overdue_followups'],
            'stale_leads' => $coreMetrics['inactive_leads'], // mapping inactive to stale for backwards compatibility with controller
            'leads_by_source' => $this->sourceAnalytics->getMetrics($startDate, $endDate, $userId),
            'leads_by_pipeline' => $this->pipelineAnalytics->getMetrics($startDate, $endDate, $userId),
            'leads_by_owner' => $this->ownerAnalytics->getMetrics($startDate, $endDate),
            'lead_trend' => $this->trendAnalytics->getMetrics($startDate, $endDate, $userId),
            'aging_leads_avg_days' => $coreMetrics['aging_leads_avg_days'],
            'avg_response_time_hours' => $coreMetrics['avg_response_time_hours'],
            'score_distribution' => $coreMetrics['score_distribution'],
        ];
    }
}
