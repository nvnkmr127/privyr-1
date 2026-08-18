<?php

namespace Webkul\Lead\Services;

use Illuminate\Support\Facades\DB;
use Webkul\Lead\Models\Lead;

class LeadAnalyticsService
{
    /**
     * Get aggregate analytics for a given date range and optional user.
     */
    public function getMetrics(string $startDate, string $endDate, ?int $userId = null): array
    {
        $query = Lead::whereBetween('created_at', [$startDate, $endDate]);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $baseLeads = $query->get();
        $totalLeads = $baseLeads->count();

        // 1. Acquisition & Conversion
        $qualified = $baseLeads->where('qualification_status', 'qualified')->count();
        $unqualified = $baseLeads->where('qualification_status', 'unqualified')->count();

        $wonStageIds = DB::table('lead_pipeline_stages')->where('code', 'won')->pluck('id')->toArray();
        $lostStageIds = DB::table('lead_pipeline_stages')->where('code', 'lost')->pluck('id')->toArray();

        $wonLeads = $baseLeads->whereIn('lead_pipeline_stage_id', $wonStageIds)->count();
        $lostLeads = $baseLeads->whereIn('lead_pipeline_stage_id', $lostStageIds)->count();

        $conversionRate = $totalLeads > 0 ? round(($wonLeads / $totalLeads) * 100, 1) : 0;

        // Breakdown by Source
        $leadsBySource = $baseLeads->groupBy('lead_source_id')->map(function ($group) {
            $sourceName = $group->first()->source?->name ?? 'Direct/Unknown';

            return [
                'name' => $sourceName,
                'count' => $group->count(),
                'id' => $group->first()->lead_source_id,
            ];
        })->sortByDesc('count')->values()->toArray();

        // Breakdown by Pipeline
        $leadsByPipeline = $baseLeads->groupBy('lead_pipeline_id')->map(function ($group) {
            $pipelineName = $group->first()->pipeline?->name ?? 'Default';

            return [
                'name' => $pipelineName,
                'count' => $group->count(),
                'id' => $group->first()->lead_pipeline_id,
            ];
        })->sortByDesc('count')->values()->toArray();

        // 2. Pipeline & Health
        $staleLeads = $baseLeads->filter(function ($lead) {
            return $lead->health_state === 'stale';
        })->count();

        $agingLeadsAvg = $totalLeads > 0
            ? round($baseLeads->avg(function ($lead) {
                return $lead->created_at->diffInDays(now());
            }), 1)
            : 0;

        // Score Distribution (0-20, 21-40, 41-60, 61-80, 81-100)
        $scoreDistribution = [
            '0-20' => $baseLeads->whereBetween('lead_score', [0, 20])->count(),
            '21-40' => $baseLeads->whereBetween('lead_score', [21, 40])->count(),
            '41-60' => $baseLeads->whereBetween('lead_score', [41, 60])->count(),
            '61-80' => $baseLeads->whereBetween('lead_score', [61, 80])->count(),
            '81-100' => $baseLeads->whereBetween('lead_score', [81, 100])->count(),
        ];

        // 3. Velocity & Effort
        // Response time (time from creation to first activity)
        // Assignment time (time from creation to first owner assigned)
        $totalResponseHours = 0;
        $respondedLeadsCount = 0;

        foreach ($baseLeads as $lead) {
            $firstActivity = $lead->activities()->where('type', '!=', 'system')->orderBy('created_at', 'asc')->first();
            if ($firstActivity) {
                $totalResponseHours += $firstActivity->created_at->diffInHours($lead->created_at);
                $respondedLeadsCount++;
            }
        }

        $avgResponseTimeHours = $respondedLeadsCount > 0 ? round($totalResponseHours / $respondedLeadsCount, 1) : 0;

        // Overdue follow-ups
        $overdueFollowups = $baseLeads->filter(function ($lead) {
            return $lead->follow_up_state === 'Overdue';
        })->count();

        // 4. Team Performance
        $leadsByOwner = $baseLeads->groupBy('user_id')->map(function ($group) {
            $ownerName = $group->first()->user?->name ?? 'Unassigned';

            return [
                'name' => $ownerName,
                'count' => $group->count(),
                'id' => $group->first()->user_id,
            ];
        })->sortByDesc('count')->values()->toArray();

        return [
            'total_leads' => $totalLeads,
            'qualified_leads' => $qualified,
            'unqualified_leads' => $unqualified,
            'won_leads' => $wonLeads,
            'lost_leads' => $lostLeads,
            'conversion_rate' => $conversionRate,
            'leads_by_source' => $leadsBySource,
            'leads_by_pipeline' => $leadsByPipeline,
            'leads_by_owner' => $leadsByOwner,
            'stale_leads' => $staleLeads,
            'aging_leads_avg_days' => $agingLeadsAvg,
            'score_distribution' => $scoreDistribution,
            'avg_response_time_hours' => $avgResponseTimeHours,
            'overdue_followups' => $overdueFollowups,
        ];
    }
}
