<?php

namespace Webkul\Lead\Services\Analytics;

use Illuminate\Support\Facades\DB;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Services\Analytics\Traits\AclFilteringTrait;
use Webkul\Lead\Services\Analytics\Traits\DateFilteringTrait;

class LeadPipelineAnalyticsService
{
    use AclFilteringTrait, DateFilteringTrait;

    /**
     * Get lead pipeline performance metrics.
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

        $query->select(
            'leads.lead_pipeline_id as id',
            DB::raw('COALESCE(lead_pipelines.name, \'Default\') as name'),
            DB::raw('COUNT(leads.id) as count')
        );

        $query->leftJoin('lead_pipelines', 'leads.lead_pipeline_id', '=', 'lead_pipelines.id');
        $query->groupBy('leads.lead_pipeline_id', 'lead_pipelines.name');
        $query->orderByDesc('count');

        $results = $query->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'count' => (int) $item->count,
            ];
        });

        return $results->toArray();
    }
}
