<?php

namespace Webkul\Lead\Services\Analytics;

use Illuminate\Support\Facades\DB;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Services\Analytics\Traits\AclFilteringTrait;
use Webkul\Lead\Services\Analytics\Traits\DateFilteringTrait;

class LeadSourceAnalyticsService
{
    use AclFilteringTrait, DateFilteringTrait;

    /**
     * Get lead source performance metrics.
     */
    public function getMetrics(string $startDate, string $endDate, ?int $userId = null): array
    {
        $query = Lead::query();
        
        $this->applyDateFiltering($query, $startDate, $endDate, 'leads.created_at');
        
        if ($userId) {
            $query->where('leads.user_id', $userId);
        } else {
            $this->applyAclFiltering($query);
        }

        $wonStageIds = DB::table('lead_pipeline_stages')->where('code', 'won')->pluck('id')->toArray();
        $lostStageIds = DB::table('lead_pipeline_stages')->where('code', 'lost')->pluck('id')->toArray();
        
        $wonStagesCsv = !empty($wonStageIds) ? implode(',', $wonStageIds) : '0';
        $lostStagesCsv = !empty($lostStageIds) ? implode(',', $lostStageIds) : '0';

        $query->select(
            'leads.lead_source_id as id',
            DB::raw('COALESCE(lead_sources.name, \'Direct/Unknown\') as name'),
            DB::raw('COUNT(leads.id) as count'),
            DB::raw("SUM(CASE WHEN leads.qualification_status = 'qualified' THEN 1 ELSE 0 END) as qualified_leads"),
            DB::raw("SUM(CASE WHEN leads.lead_pipeline_stage_id IN ({$wonStagesCsv}) THEN 1 ELSE 0 END) as won_leads"),
            DB::raw("SUM(CASE WHEN leads.lead_pipeline_stage_id IN ({$lostStagesCsv}) THEN 1 ELSE 0 END) as lost_leads")
        );

        $query->leftJoin('lead_sources', 'leads.lead_source_id', '=', 'lead_sources.id');
        $query->groupBy('leads.lead_source_id', 'lead_sources.name');
        $query->orderByDesc('count');

        $results = $query->get()->map(function ($item) {
            $total = (int) $item->count;
            $won = (int) $item->won_leads;
            $qualified = (int) $item->qualified_leads;

            return [
                'id' => $item->id,
                'name' => $item->name,
                'count' => $total,
                'qualified_leads' => $qualified,
                'won_leads' => $won,
                'lost_leads' => (int) $item->lost_leads,
                'qualification_rate' => $total > 0 ? round(($qualified / $total) * 100, 1) : 0,
                'conversion_rate' => $total > 0 ? round(($won / $total) * 100, 1) : 0,
            ];
        });

        return $results->toArray();
    }
}
