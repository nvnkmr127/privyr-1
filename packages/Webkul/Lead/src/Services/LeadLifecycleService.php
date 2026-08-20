<?php

namespace Webkul\Lead\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Event;
use Webkul\Lead\Contracts\Lead;
use Webkul\Lead\Repositories\LeadRepository;
use Webkul\Lead\Repositories\LeadStatusHistoryRepository;

class LeadLifecycleService
{
    /**
     * Valid Lead Statuses
     */
    const STATUS_OPEN = 'Open';

    const STATUS_WORKING = 'Working';

    const STATUS_NURTURING = 'Nurturing';

    const STATUS_CONVERTED = 'Converted';

    const STATUS_LOST = 'Lost';

    const STATUS_JUNK = 'Junk';

    public function __construct(
        protected LeadRepository $leadRepository,
        protected LeadStatusHistoryRepository $leadStatusHistoryRepository
    ) {}

    /**
     * Get valid statuses.
     */
    public function getValidStatuses(): array
    {
        return [
            self::STATUS_OPEN,
            self::STATUS_WORKING,
            self::STATUS_NURTURING,
            self::STATUS_CONVERTED,
            self::STATUS_LOST,
            self::STATUS_JUNK,
        ];
    }

    /**
     * Update Lead Status and record history/events.
     *
     * @throws \Exception
     */
    public function changeStatus(Lead $lead, string $newStatus, ?string $reason = null, ?int $userId = null): bool
    {
        if (! in_array($newStatus, $this->getValidStatuses())) {
            throw new \InvalidArgumentException("Invalid lead status: {$newStatus}");
        }

        $currentStatus = $lead->status ?? self::STATUS_OPEN;

        if ($currentStatus === $newStatus) {
            return true; // No change
        }

        // Validate transitions
        $this->validateTransition($currentStatus, $newStatus);

        // Additional update data based on status
        $updateData = ['status' => $newStatus];

        if ($newStatus === self::STATUS_CONVERTED) {
            $updateData['converted_at'] = Carbon::now();
            $updateData['converted_by'] = $userId ?? (auth()->check() ? auth()->id() : null);
        }

        if ($newStatus === self::STATUS_LOST) {
            $updateData['lost_reason'] = $reason;
        }

        if ($newStatus === self::STATUS_JUNK) {
            $updateData['junk_reason'] = $reason;
        }

        // Apply changes directly via Eloquent to avoid triggering LeadRepository loops
        // since LeadRepository might have its own logic. But using Repository is preferred.
        // We will temporarily unguard or just update what's needed.
        // It's better to update using eloquent or repository carefully.

        // Before updating, save the current state for event
        $previousStatus = $lead->status;

        // Update Lead
        $lead->update($updateData);

        $lead->refresh();

        $userId = $userId ?? (auth()->check() ? auth()->id() : null);

        // Record History
        $this->leadStatusHistoryRepository->create([
            'lead_id' => $lead->id,
            'previous_status' => $previousStatus,
            'new_status' => $newStatus,
            'user_id' => $userId,
            'reason' => $reason,
        ]);

        // Dispatch Events
        $this->dispatchEvents($lead, $previousStatus, $newStatus, $userId);

        return true;
    }

    /**
     * Validate if a transition is allowed.
     */
    protected function validateTransition(string $current, string $new)
    {
        // Converted Leads cannot silently be reopened
        if ($current === self::STATUS_CONVERTED) {
            throw new \Exception('A converted lead cannot change its status automatically. It must be explicitly handled if at all.');
        }

        // Lost or Junk can only go back to working if explicitly reopened (we handle this as allowed but caller must explicitly do it)
        // If we want to strictly enforce it, we could have a `reopen()` method instead.
        // For now, we allow the transition but controllers should use `reopen()` action to signify explicit intent.
    }

    /**
     * Reopen a Lost or Junk Lead.
     */
    public function reopenLead(Lead $lead, ?string $reason = null, ?int $userId = null): bool
    {
        $current = $lead->status;

        if (! in_array($current, [self::STATUS_LOST, self::STATUS_JUNK])) {
            throw new \Exception('Only Lost or Junk leads can be reopened.');
        }

        // Reopening usually sets it back to Working
        $success = $this->changeStatus($lead, self::STATUS_WORKING, $reason, $userId);

        if ($success) {
            Event::dispatch('lead.status.reopened', [
                'lead' => $lead,
                'previous_status' => $current,
                'user_id' => $userId ?? (auth()->check() ? auth()->id() : null),
                'timestamp' => Carbon::now(),
            ]);
        }

        return $success;
    }

    /**
     * Dispatch specific events.
     */
    protected function dispatchEvents(Lead $lead, ?string $previousStatus, string $newStatus, ?int $userId)
    {
        $eventData = [
            'lead' => $lead,
            'previous_status' => $previousStatus,
            'new_status' => $newStatus,
            'user_id' => $userId,
            'timestamp' => Carbon::now(),
        ];

        // Generic Event
        Event::dispatch('lead.status.changed', $eventData);

        // Specific Events based on new status
        $specificEventName = match ($newStatus) {
            self::STATUS_OPEN => 'lead.status.opened',
            self::STATUS_WORKING => 'lead.status.working',
            self::STATUS_NURTURING => 'lead.status.nurturing',
            self::STATUS_CONVERTED => 'lead.status.converted',
            self::STATUS_LOST => 'lead.status.lost',
            self::STATUS_JUNK => 'lead.status.junk',
            default => null,
        };

        if ($specificEventName) {
            Event::dispatch($specificEventName, $eventData);
        }
    }
}
