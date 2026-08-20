<?php

namespace Webkul\Lead\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Webkul\Lead\Repositories\LeadRepository;
use Webkul\Lead\Models\LeadMergeHistory;

class LeadMergeService
{
    public function __construct(
        protected LeadRepository $leadRepository
    ) {}

    /**
     * Merge one lead into another.
     *
     * @param int $survivingId The ID of the lead that will be kept.
     * @param int $mergedId The ID of the lead that will be soft-deleted (merged).
     * @param array $fieldSelections Key-value pairs of fields to update on the surviving lead.
     * @param int $userId The ID of the user performing the merge.
     * @param string|null $reason Optional reason for the merge.
     * @return bool
     * @throws Exception
     */
    public function merge(int $survivingId, int $mergedId, array $fieldSelections, int $userId, ?string $reason = null): bool
    {
        if ($survivingId === $mergedId) {
            throw new Exception('A lead cannot be merged into itself.');
        }

        $survivingLead = $this->leadRepository->find($survivingId);
        $mergedLead = $this->leadRepository->find($mergedId);

        if (!$survivingLead || !$mergedLead) {
            throw new Exception('One or both leads not found.');
        }

        if ($survivingLead->is_merged || $mergedLead->is_merged) {
            throw new Exception('Cannot merge already merged leads.');
        }

        // Add ACL check here if needed or let controller handle it.
        // Controller should handle visibility scope.

        DB::beginTransaction();

        try {
            // 1. Reassign Relationships
            // Activities
            $mergedLead->activities()->update(['lead_id' => $survivingId]);
            // Qualifications
            $mergedLead->qualifications()->update(['lead_id' => $survivingId]);
            // Assignments
            $mergedLead->assignments()->update(['lead_id' => $survivingId]);
            // Nurture Enrollments
            $mergedLead->nurtureEnrollments()->update(['lead_id' => $survivingId]);
            // Emails
            $mergedLead->emails()->update(['lead_id' => $survivingId]);
            // Attribution Histories
            $mergedLead->attributionHistories()->update(['lead_id' => $survivingId]);

            // Tags (Many-to-Many)
            $existingTags = $survivingLead->tags->pluck('id')->toArray();
            $newTags = $mergedLead->tags->pluck('id')->toArray();
            $allTags = array_unique(array_merge($existingTags, $newTags));
            $survivingLead->tags()->sync($allTags);

            // Custom Attributes
            // In EAV, attribute values are tied to entity_id and entity_type.
            // Since we don't have the exact EAV structure here in simple models without the generic EAV service,
            // we rely on the `$fieldSelections` to update the model. The BaseRepository's `update` handles EAV.
            
            // 2. Update Surviving Lead Fields
            if (!empty($fieldSelections)) {
                $this->leadRepository->update($fieldSelections, $survivingId);
            }

            // 3. Mark Merged Lead as Merged (Soft Delete equivalent)
            $mergedLead->update([
                'is_merged' => true,
                'merged_into_id' => $survivingId,
                'duplicate_status' => 'confirmed_duplicate',
            ]);

            // Update surviving lead duplicate status if it was a possible duplicate
            if ($survivingLead->duplicate_status === 'possible_duplicate') {
                $survivingLead->update(['duplicate_status' => 'clean']);
            }

            // 4. Record Merge History
            LeadMergeHistory::create([
                'surviving_lead_id' => $survivingId,
                'merged_lead_id' => $mergedId,
                'user_id' => $userId,
                'merged_data' => $fieldSelections,
                'merge_reason' => $reason,
            ]);

            // 5. Fire Event
            event('lead.merged', ['surviving_lead' => $survivingLead, 'merged_lead' => $mergedLead]);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
