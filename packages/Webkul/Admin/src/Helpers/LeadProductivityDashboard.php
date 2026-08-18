<?php

namespace Webkul\Admin\Helpers;

use Carbon\Carbon;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Repositories\LeadRepository;

class LeadProductivityDashboard
{
    public function __construct(
        protected LeadRepository $leadRepository
    ) {}

    /**
     * Get productivity metrics.
     */
    public function getMetrics(): array
    {
        // 1. New Leads (arrived today)
        $newLeadsCount = $this->leadRepository->findWhere([
            ['created_at', '>=', Carbon::today()],
        ])->count();

        // 2. Unassigned
        $unassignedCount = $this->leadRepository->findWhere([
            'user_id' => null,
        ])->count();

        // 3. Needs first contact (unread or no contact)
        $needsContactCount = $this->leadRepository->getNeedsAttentionLeads()->count();

        // 4. Follow-ups Due
        $dueTodayCount = $this->leadRepository->getDueTodayLeads()->count();

        // 5. Overdue
        $overdueCount = $this->leadRepository->getOverdueLeads()->count();

        // 6. Hot Leads
        $hotCount = $this->leadRepository->findWhereIn('priority', ['high', 'urgent'])->count();

        // 7. Stale
        $staleCount = $this->leadRepository->getStaleLeads()->count();

        // 8. No next action
        $noActionCount = $this->leadRepository->getNoNextActionLeads()->count();

        // 9. Qualified
        $qualifiedCount = $this->leadRepository->findWhere([
            'qualification_status' => 'qualified',
        ])->count();

        // 10. Stage changed today (Optional, using a basic check on updated_at for now,
        // true implementation would require activity history check, which we can approximate)
        // Here we approximate as updated today and stage is not "new"
        $stageChangedToday = $this->leadRepository->findWhere([
            ['updated_at', '>=', Carbon::today()],
        ])->count();

        // 11. Waiting for response
        $waitingCount = $this->leadRepository->findWhere([
            'is_unread' => false,
            ['last_contacted_at', '!=', null],
        ])->count();

        return [
            'new_leads' => $newLeadsCount,
            'unassigned' => $unassignedCount,
            'needs_contact' => $needsContactCount,
            'due_today' => $dueTodayCount,
            'overdue' => $overdueCount,
            'hot' => $hotCount,
            'stale' => $staleCount,
            'no_next_action' => $noActionCount,
            'qualified' => $qualifiedCount,
            'stage_changed' => $stageChangedToday,
            'waiting_for_response' => $waitingCount,
        ];
    }

    /**
     * Get next actions for the current user.
     */
    public function getMyNextActions()
    {
        $userId = auth()->guard('user')->id();

        if (! $userId) {
            return collect();
        }

        return app(Lead::class)
            ->whereNotNull('next_action')
            ->whereNotNull('next_follow_up_at')
            ->where('follow_up_owner_id', $userId)
            ->whereDate('next_follow_up_at', '<=', Carbon::today())
            ->orderBy('next_follow_up_at', 'asc')
            ->limit(10)
            ->get();
    }
}
