<?php

namespace Webkul\Admin\Helpers;

use Carbon\Carbon;
use Webkul\Lead\Repositories\LeadRepository;
use Webkul\Lead\Models\LeadProxy;
use Webkul\Activity\Models\ActivityProxy;
use Illuminate\Database\Eloquent\Builder;

class LeadProductivityDashboard
{
    public function __construct(
        protected LeadRepository $leadRepository
    ) {}

    /**
     * Get productivity metrics for the dashboard.
     */
    public function getMetrics(array $filters = []): array
    {
        return [
            'today' => $this->getTodayMetrics($filters),
            'pipeline' => $this->getPipelineMetrics($filters),
            'health' => $this->getHealthMetrics($filters),
        ];
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        // Owner/Team filter (ACL is applied elsewhere if needed, but this applies explicit filters)
        if (!empty($filters['user_id'])) {
            $query->where('leads.user_id', $filters['user_id']);
        } elseif (empty($filters['bypass_acl'])) {
            // Apply ACL if not explicitly bypassed
            $query->visibleTo(auth()->user());
        }

        // Source filter
        if (!empty($filters['source_id'])) {
            $query->where('leads.lead_source_id', $filters['source_id']);
        }

        // Date filter for generic queries (if applicable)
        if (!empty($filters['date_range'])) {
            $dates = $this->getDateRange($filters);
            if ($dates) {
                $query->whereBetween('leads.created_at', $dates);
            }
        }

        return $query;
    }

    protected function getDateRange(array $filters): ?array
    {
        $range = $filters['date_range'] ?? null;
        
        return match ($range) {
            'today' => [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()],
            'yesterday' => [Carbon::yesterday()->startOfDay(), Carbon::yesterday()->endOfDay()],
            'last_7_days' => [Carbon::today()->subDays(7)->startOfDay(), Carbon::today()->endOfDay()],
            'last_30_days' => [Carbon::today()->subDays(30)->startOfDay(), Carbon::today()->endOfDay()],
            'custom' => !empty($filters['start_date']) && !empty($filters['end_date']) 
                ? [Carbon::parse($filters['start_date'])->startOfDay(), Carbon::parse($filters['end_date'])->endOfDay()]
                : null,
            default => null,
        };
    }

    protected function getBaseLeadQuery(array $filters = []): Builder
    {
        $query = LeadProxy::modelClass()::query();
        return $this->applyFilters($query, $filters);
    }

    /**
     * TODAY metrics
     */
    protected function getTodayMetrics(array $filters): array
    {
        $baseQuery = $this->getBaseLeadQuery($filters);

        // New Leads Today
        $newLeadsCount = (clone $baseQuery)
            ->whereDate('leads.created_at', Carbon::today())
            ->count();

        // Follow-ups Due Today
        $activityQuery = ActivityProxy::modelClass()::query()
            ->where('status', 'pending')
            ->whereDate('schedule_from', Carbon::today());
        
        if (!empty($filters['user_id'])) {
            $activityQuery->where('user_id', $filters['user_id']);
        } elseif (empty($filters['bypass_acl'])) {
            $visibilityService = app(\Webkul\Lead\Services\LeadVisibilityService::class);
            $userIds = $visibilityService->getVisibleUserIds(auth()->user());
            if ($userIds !== null) {
                $activityQuery->whereIn('user_id', $userIds);
            }
        }
        $dueTodayCount = $activityQuery->count();

        // Overdue Follow-ups
        $overdueActivityQuery = ActivityProxy::modelClass()::query()
            ->where('status', 'pending')
            ->where('schedule_from', '<', Carbon::now());
            
        if (!empty($filters['user_id'])) {
            $overdueActivityQuery->where('user_id', $filters['user_id']);
        } elseif (empty($filters['bypass_acl'])) {
            $visibilityService = app(\Webkul\Lead\Services\LeadVisibilityService::class);
            $userIds = $visibilityService->getVisibleUserIds(auth()->user());
            if ($userIds !== null) {
                $overdueActivityQuery->whereIn('user_id', $userIds);
            }
        }
        $overdueCount = $overdueActivityQuery->count();

        // Unassigned Leads
        $unassignedCount = (clone $baseQuery)
            ->whereNull('leads.user_id')
            ->count();

        // High-priority Leads
        $highPriorityCount = (clone $baseQuery)
            ->whereIn('leads.priority', ['high', 'urgent'])
            ->count();

        return [
            'new_leads' => $newLeadsCount,
            'due_today' => $dueTodayCount,
            'overdue' => $overdueCount,
            'unassigned' => $unassignedCount,
            'high_priority' => $highPriorityCount,
        ];
    }

    /**
     * LEAD PIPELINE metrics
     */
    protected function getPipelineMetrics(array $filters): array
    {
        $baseQuery = $this->getBaseLeadQuery($filters);

        // We assume stage codes might be used, or specific statuses
        // Open Leads (not won, not lost)
        $openCount = (clone $baseQuery)
            ->whereHas('stage', fn ($q) => $q->whereNotIn('code', ['won', 'lost']))
            ->count();
            
        // Qualified Leads
        $qualifiedCount = (clone $baseQuery)
            ->where('qualification_status', 'qualified')
            ->count();

        // Nurturing Leads
        $nurturingCount = (clone $baseQuery)
            ->where('status', 'Nurturing')
            ->count();

        // Converted Leads (Won)
        $convertedCount = (clone $baseQuery)
            ->whereHas('stage', fn ($q) => $q->where('code', 'won'))
            ->count();

        // Lost Leads
        $lostCount = (clone $baseQuery)
            ->whereHas('stage', fn ($q) => $q->where('code', 'lost'))
            ->count();

        return [
            'open' => $openCount,
            'qualified' => $qualifiedCount,
            'nurturing' => $nurturingCount,
            'converted' => $convertedCount,
            'lost' => $lostCount,
        ];
    }

    /**
     * LEAD HEALTH metrics
     */
    protected function getHealthMetrics(array $filters): array
    {
        $baseQuery = $this->getBaseLeadQuery($filters);

        // Needs Attention
        $needsAttentionCount = (clone $baseQuery)
            ->where(function ($query) {
                $query->where('is_unread', true)
                    ->orWhereNull('last_contacted_at');
            })
            ->count();

        // Inactive (Stale) -> No contact for > 14 days
        $inactiveDays = config('lead_health.inactivity.inactive_days', 14);
        $inactiveCount = (clone $baseQuery)
            ->whereNotNull('last_contacted_at')
            ->where('last_contacted_at', '<', Carbon::now()->subDays($inactiveDays))
            ->where('status', '!=', 'Nurturing')
            ->count();
            
        // Overdue Leads
        $overdueLeadsCount = (clone $baseQuery)
            ->whereNotNull('next_follow_up_at')
            ->where('next_follow_up_at', '<', Carbon::now())
            ->count();
            
        // Leads with no Follow-up
        $noFollowUpCount = (clone $baseQuery)
            ->whereNull('next_follow_up_at')
            ->whereNull('next_action')
            ->count();

        return [
            'needs_attention' => $needsAttentionCount,
            'inactive' => $inactiveCount,
            'overdue' => $overdueLeadsCount,
            'no_follow_up' => $noFollowUpCount,
        ];
    }

    /**
     * Get next actions for the current user.
     */
    public function getMyNextActions(array $filters = [])
    {
        $query = ActivityProxy::modelClass()::with('lead')
            ->where('status', 'pending');
            
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        } else {
            $userId = auth()->guard('user')->id();
            if ($userId) {
                $query->where('user_id', $userId);
            }
        }
            
        return $query->whereDate('schedule_from', '<=', Carbon::today())
            ->orderBy('schedule_from', 'asc')
            ->limit(10)
            ->get();
    }
}
