<?php

namespace Webkul\Lead\Services;

use Illuminate\Support\Facades\DB;
use Webkul\Lead\Repositories\LeadRepository;

class LeadAttributionReportingService
{
    public function __construct(
        protected LeadRepository $leadRepository
    ) {}

    /**
     * Get aggregate metrics grouped by a specific attribution field (e.g., 'first_lead_source_id' or 'first_campaign').
     */
    protected function getMetricsByField(string $field): array
    {
        $query = $this->leadRepository->getModel()
            ->select($field, DB::raw('COUNT(*) as total_leads'))
            ->addSelect(DB::raw("SUM(CASE WHEN qualification_status = 'qualified' THEN 1 ELSE 0 END) as qualified_leads"))
            ->addSelect(DB::raw("SUM(CASE WHEN status = 1 OR closed_at IS NOT NULL AND lead_pipeline_stage_id IN (SELECT id FROM lead_stages WHERE code = 'won') THEN 1 ELSE 0 END) as converted_leads")) // Naive conversion check
            ->addSelect(DB::raw("SUM(CASE WHEN status = 0 OR closed_at IS NOT NULL AND lead_pipeline_stage_id IN (SELECT id FROM lead_stages WHERE code = 'lost') THEN 1 ELSE 0 END) as lost_leads"))
            ->whereNotNull($field)
            ->groupBy($field)
            ->get();

        $results = [];
        foreach ($query as $row) {
            $total = (int) $row->total_leads;
            $qualified = (int) $row->qualified_leads;
            $converted = (int) $row->converted_leads;

            $results[$row->$field] = [
                'total_leads' => $total,
                'qualified_leads' => $qualified,
                'converted_leads' => $converted,
                'lost_leads' => (int) $row->lost_leads,
                'qualification_rate' => $total > 0 ? round(($qualified / $total) * 100, 2) : 0,
                'conversion_rate' => $total > 0 ? round(($converted / $total) * 100, 2) : 0,
            ];
        }

        return $results;
    }

    public function getMetricsByFirstSource(): array
    {
        return $this->getMetricsByField('first_lead_source_id');
    }

    public function getMetricsByFirstCampaign(): array
    {
        return $this->getMetricsByField('first_campaign');
    }

    public function getMetricsByLatestSource(): array
    {
        return $this->getMetricsByField('latest_lead_source_id');
    }

    public function getMetricsByLatestCampaign(): array
    {
        return $this->getMetricsByField('latest_campaign');
    }

    public function getMetricsByOrigin(): array
    {
        return $this->getMetricsByField('first_origin');
    }
}
