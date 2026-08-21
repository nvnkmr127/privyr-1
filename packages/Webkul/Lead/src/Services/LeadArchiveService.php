<?php

namespace Webkul\Lead\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Webkul\Lead\Contracts\Lead;
use Webkul\Lead\Repositories\LeadRepository;

class LeadArchiveService
{
    public function __construct(
        protected LeadRepository $leadRepository
    ) {}

    /**
     * Archive a Lead.
     * 
     * @param Lead $lead
     * @param string|null $reason
     * @param int|null $userId
     * @return bool
     * @throws \Exception
     */
    public function archive(Lead $lead, ?string $reason = null, ?int $userId = null): bool
    {
        if ($lead->is_archived) {
            return true;
        }

        // Check if user is authorized to update the lead
        if (! Gate::allows('update', $lead)) {
            throw new \Exception('Unauthorized to archive this lead.');
        }

        // Direct DB update to avoid heavy repository events/loops if necessary,
        // but we'll use eloquent to trigger model events.
        $lead->is_archived = true;
        $lead->save();

        $userId = $userId ?? (auth()->check() ? auth()->id() : null);

        // Dispatch specific event
        Event::dispatch('lead.archived', [
            'lead' => $lead,
            'reason' => $reason,
            'user_id' => $userId,
            'timestamp' => Carbon::now(),
        ]);

        return true;
    }

    /**
     * Restore an archived Lead.
     * 
     * @param Lead $lead
     * @param string|null $reason
     * @param int|null $userId
     * @return bool
     * @throws \Exception
     */
    public function restore(Lead $lead, ?string $reason = null, ?int $userId = null): bool
    {
        if (! $lead->is_archived) {
            return true;
        }

        // Check if user is authorized to update the lead
        if (! Gate::allows('update', $lead)) {
            throw new \Exception('Unauthorized to restore this lead.');
        }

        $lead->is_archived = false;
        $lead->save();

        $userId = $userId ?? (auth()->check() ? auth()->id() : null);

        // Dispatch specific event
        Event::dispatch('lead.restored', [
            'lead' => $lead,
            'reason' => $reason,
            'user_id' => $userId,
            'timestamp' => Carbon::now(),
        ]);

        return true;
    }

    /**
     * Permanently delete a Lead.
     * 
     * @param Lead $lead
     * @param int|null $userId
     * @return bool
     * @throws \Exception
     */
    public function permanentDelete(Lead $lead, ?int $userId = null): bool
    {
        // Check if user is authorized to delete the lead
        if (! Gate::allows('delete', $lead)) {
            throw new \Exception('Unauthorized to permanently delete this lead.');
        }

        $userId = $userId ?? (auth()->check() ? auth()->id() : null);

        Event::dispatch('lead.deleting.permanently', [
            'lead_id' => $lead->id,
            'user_id' => $userId,
            'timestamp' => Carbon::now(),
        ]);

        return $this->leadRepository->delete($lead->id);
    }
}
