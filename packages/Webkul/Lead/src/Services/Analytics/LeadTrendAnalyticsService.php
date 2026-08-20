<?php

namespace Webkul\Lead\Services\Analytics;

use Illuminate\Support\Facades\DB;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Services\Analytics\Traits\AclFilteringTrait;
use Webkul\Lead\Services\Analytics\Traits\DateFilteringTrait;

class LeadTrendAnalyticsService
{
    use AclFilteringTrait, DateFilteringTrait;

    /**
     * Get lead creation trend over time.
     *
     * @param  string  $groupBy  'day', 'week', or 'month'
     */
    public function getMetrics(string $startDate, string $endDate, ?int $userId = null, string $groupBy = 'day'): array
    {
        $query = Lead::query();

        $this->applyDateFiltering($query, $startDate, $endDate, 'leads.created_at');

        if ($userId) {
            $query->where('leads.user_id', $userId);
        } else {
            $this->applyAclFiltering($query);
        }

        $dateFormat = match ($groupBy) {
            'month' => '%Y-%m',
            'week' => '%Y-%v', // %v is week number
            default => '%Y-%m-%d',
        };

        $query->select(
            DB::raw("DATE_FORMAT(leads.created_at, '{$dateFormat}') as date_group"),
            DB::raw('COUNT(leads.id) as count')
        );

        $query->groupBy('date_group');
        $query->orderBy('date_group');

        return $query->get()->toArray();
    }
}
