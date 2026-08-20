<?php

namespace Webkul\Lead\Services\Analytics;

use Illuminate\Support\Facades\DB;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Services\Analytics\Traits\AclFilteringTrait;
use Webkul\Lead\Services\Analytics\Traits\DateFilteringTrait;

class LeadCoreAnalyticsService
{
    use AclFilteringTrait, DateFilteringTrait;

    /**
     * Get core lead metrics for a given date range.
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

        // We use single aggregation query to avoid loading models into memory.
        $wonStageIds = DB::table('lead_pipeline_stages')->where('code', 'won')->pluck('id')->toArray();
        $lostStageIds = DB::table('lead_pipeline_stages')->where('code', 'lost')->pluck('id')->toArray();

        // Convert arrays to comma separated for raw query if not empty, otherwise use '0' to avoid SQL syntax errors
        $wonStagesCsv = ! empty($wonStageIds) ? implode(',', $wonStageIds) : '0';
        $lostStagesCsv = ! empty($lostStageIds) ? implode(',', $lostStageIds) : '0';

        $selects = [
            'COUNT(leads.id) as total_leads',
            'SUM(CASE WHEN leads.qualification_status = \'qualified\' THEN 1 ELSE 0 END) as qualified_leads',
            'SUM(CASE WHEN leads.qualification_status = \'unqualified\' THEN 1 ELSE 0 END) as unqualified_leads',
            'SUM(CASE WHEN leads.lead_pipeline_stage_id IN ('.$wonStagesCsv.') THEN 1 ELSE 0 END) as won_leads',
            'SUM(CASE WHEN leads.lead_pipeline_stage_id IN ('.$lostStagesCsv.') THEN 1 ELSE 0 END) as lost_leads',
            'SUM(CASE WHEN leads.status = \'Nurturing\' THEN 1 ELSE 0 END) as nurturing_leads',
            'SUM(CASE WHEN leads.junk_reason IS NOT NULL THEN 1 ELSE 0 END) as junk_leads',
            'SUM(CASE WHEN leads.user_id IS NULL THEN 1 ELSE 0 END) as unassigned_leads',
            'SUM(CASE WHEN leads.status = \'Open\' THEN 1 ELSE 0 END) as open_leads',
            'SUM(CASE WHEN leads.status = \'Working\' THEN 1 ELSE 0 END) as working_leads',
        ];

        // SQL calculation for "needs attention" and "inactive" based on model's health state logic
        // Inactive: no activity for 14 days
        // Needs Attention: no activity for 7 days
        $inactiveDays = config('lead_health.inactivity.inactive_days', 14);
        $needsAttentionDays = config('lead_health.inactivity.needs_attention_days', 7);

        $inactiveThreshold = now()->subDays($inactiveDays)->toDateTimeString();
        $needsAttentionThreshold = now()->subDays($needsAttentionDays)->toDateTimeString();

        $selects[] = "SUM(CASE WHEN leads.status != 'Nurturing' AND (leads.last_activity_at <= '{$inactiveThreshold}' OR (leads.last_activity_at IS NULL AND leads.created_at <= '{$inactiveThreshold}')) THEN 1 ELSE 0 END) as inactive_leads";

        $selects[] = "SUM(CASE WHEN leads.status != 'Nurturing' AND (leads.last_activity_at <= '{$needsAttentionThreshold}' OR (leads.last_activity_at IS NULL AND leads.created_at <= '{$needsAttentionThreshold}')) AND (leads.last_activity_at > '{$inactiveThreshold}' OR (leads.last_activity_at IS NULL AND leads.created_at > '{$inactiveThreshold}')) THEN 1 ELSE 0 END) as needs_attention_leads";

        // SQL Calculation for Follow up overdue
        $now = now()->toDateTimeString();
        $selects[] = "SUM(CASE WHEN leads.next_follow_up_at IS NOT NULL AND leads.next_follow_up_at < '{$now}' THEN 1 ELSE 0 END) as overdue_followups";

        $query->selectRaw(implode(', ', $selects));

        $result = $query->first();

        $totalLeads = (int) ($result->total_leads ?? 0);
        $wonLeads = (int) ($result->won_leads ?? 0);

        return [
            'total_leads' => $totalLeads,
            'new_leads' => $totalLeads, // In this period
            'open_leads' => (int) ($result->open_leads ?? 0),
            'working_leads' => (int) ($result->working_leads ?? 0),
            'qualified_leads' => (int) ($result->qualified_leads ?? 0),
            'unqualified_leads' => (int) ($result->unqualified_leads ?? 0),
            'nurturing_leads' => (int) ($result->nurturing_leads ?? 0),
            'won_leads' => $wonLeads,
            'lost_leads' => (int) ($result->lost_leads ?? 0),
            'junk_leads' => (int) ($result->junk_leads ?? 0),
            'unassigned_leads' => (int) ($result->unassigned_leads ?? 0),
            'inactive_leads' => (int) ($result->inactive_leads ?? 0),
            'needs_attention_leads' => (int) ($result->needs_attention_leads ?? 0),
            'overdue_followups' => (int) ($result->overdue_followups ?? 0),
            'conversion_rate' => $totalLeads > 0 ? round(($wonLeads / $totalLeads) * 100, 1) : 0,
        ];
    }
}
