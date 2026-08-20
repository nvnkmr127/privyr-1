<?php

namespace Webkul\Lead\Services;

use Illuminate\Support\Facades\DB;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Models\LeadAssignmentRule;
use Webkul\Lead\Repositories\LeadAssignmentRepository;

class LeadAssignmentService
{
    public function assignManually(Lead $lead, $userId = null, $groupId = null, $assignedBy = null): bool
    {
        $previousOwner = $lead->user_id;
        $previousGroup = $lead->group_id;

        $lead->user_id = $userId;
        $lead->group_id = $groupId;
        $lead->save();

        app(LeadAssignmentRepository::class)->create([
            'lead_id' => $lead->id,
            'assigned_to' => $userId,
            'assigned_group_id' => $groupId,
            'assigned_by' => $assignedBy ?? (auth()->check() ? auth()->id() : null),
            'previous_owner' => $previousOwner,
            'previous_group_id' => $previousGroup,
            'reason' => 'Manual Assignment',
        ]);

        if ($previousOwner || $previousGroup) {
            \Illuminate\Support\Facades\Event::dispatch('lead.reassigned', $lead);
        } else {
            \Illuminate\Support\Facades\Event::dispatch('lead.assigned', $lead);
        }

        return true;
    }

    public function unassign(Lead $lead, $unassignedBy = null): bool
    {
        $previousOwner = $lead->user_id;
        $previousGroup = $lead->group_id;

        $lead->user_id = null;
        $lead->group_id = null;
        $lead->save();

        app(LeadAssignmentRepository::class)->create([
            'lead_id' => $lead->id,
            'assigned_to' => null,
            'assigned_group_id' => null,
            'assigned_by' => $unassignedBy ?? (auth()->check() ? auth()->id() : null),
            'previous_owner' => $previousOwner,
            'previous_group_id' => $previousGroup,
            'reason' => 'Unassigned manually',
        ]);

        \Illuminate\Support\Facades\Event::dispatch('lead.unassigned', $lead);

        return true;
    }

    public function assignLead(Lead $lead): bool
    {
        // Assignment priority: WebForm owner, Rule, Default queue
        if ($lead->user_id || $lead->group_id) {
            // Already assigned manually or via WebForm
            return false;
        }

        // Get active rules ordered by sort_order
        $rules = LeadAssignmentRule::with(['conditions', 'users', 'groups'])->where('status', true)->orderBy('sort_order', 'asc')->get();

        foreach ($rules as $rule) {
            if ($this->matchRule($rule, $lead)) {
                return $this->executeRule($rule, $lead);
            }
        }

        return false;
    }

    protected function matchRule(LeadAssignmentRule $rule, Lead $lead): bool
    {
        if ($rule->conditions->isEmpty()) {
            return true; // Match all if no conditions
        }

        foreach ($rule->conditions as $condition) {
            $attribute = $condition->attribute;

            // Map common attributes
            $leadValue = null;
            if ($attribute === 'source') {
                $leadValue = $lead->source?->name ?? '';
            } elseif ($attribute === 'pipeline') {
                $leadValue = $lead->pipeline?->name ?? '';
            } else {
                $leadValue = $lead->$attribute;
            }

            if (! $this->evaluateCondition($condition->operator, $leadValue, $condition->value)) {
                return false;
            }
        }

        return true;
    }

    protected function evaluateCondition($operator, $leadValue, $ruleValue): bool
    {
        if ($leadValue === null) {
            $leadValue = '';
        }

        switch ($operator) {
            case 'equals':
                return strtolower((string) $leadValue) === strtolower((string) $ruleValue);
            case 'contains':
                return strpos(strtolower((string) $leadValue), strtolower((string) $ruleValue)) !== false;
            case '>':
                return (float) $leadValue > (float) $ruleValue;
            case '<':
                return (float) $leadValue < (float) $ruleValue;
            case '!=':
            case 'not_equals':
                return strtolower((string) $leadValue) !== strtolower((string) $ruleValue);
        }

        return false;
    }

    protected function executeRule(LeadAssignmentRule $rule, Lead $lead): bool
    {
        if ($rule->users->isEmpty() && $rule->groups->isEmpty()) {
            return false;
        }

        $assignedUserId = null;
        $assignedGroupId = null;

        if ($rule->type === 'direct') {
            if ($rule->users->isNotEmpty()) {
                $assignedUserId = $rule->users->first()->id;
            }
        } elseif ($rule->type === 'team') {
            if ($rule->groups->isNotEmpty()) {
                $assignedGroupId = $rule->groups->first()->id;
            }
        } elseif ($rule->type === 'round_robin') {
            // Find the user who was assigned least recently. Should ideally use atomic locking.
            $user = $rule->users()->orderByPivot('last_assigned_at', 'asc')->lockForUpdate()->first();
            if ($user) {
                $assignedUserId = $user->id;
                $rule->users()->updateExistingPivot($user->id, ['last_assigned_at' => now()]);
            }
        } elseif ($rule->type === 'weighted') {
            $totalWeight = $rule->users->sum('pivot.weight');
            if ($totalWeight > 0) {
                $rand = rand(1, $totalWeight);
                $current = 0;
                foreach ($rule->users as $u) {
                    $current += $u->pivot->weight;
                    if ($rand <= $current) {
                        $assignedUserId = $u->id;
                        $rule->users()->updateExistingPivot($u->id, ['last_assigned_at' => now()]);
                        break;
                    }
                }
            }
        }

        if ($assignedUserId || $assignedGroupId) {
            $previousOwner = $lead->user_id;
            $previousGroup = $lead->group_id;

            // Bypass updating Lead updated_at timestamp to avoid infinite loops if triggered via observers
            DB::table('leads')->where('id', $lead->id)->update([
                'user_id' => $assignedUserId,
                'group_id' => $assignedGroupId,
            ]);
            $lead->user_id = $assignedUserId;
            $lead->group_id = $assignedGroupId;

            // Log history
            app(LeadAssignmentRepository::class)->create([
                'lead_id' => $lead->id,
                'assigned_to' => $assignedUserId,
                'assigned_group_id' => $assignedGroupId,
                'assigned_by' => auth()->check() ? auth()->id() : null,
                'previous_owner' => $previousOwner,
                'previous_group_id' => $previousGroup,
                'reason' => "Assigned via Rule: {$rule->name}",
            ]);

            if ($previousOwner || $previousGroup) {
                \Illuminate\Support\Facades\Event::dispatch('lead.reassigned', $lead);
            } else {
                \Illuminate\Support\Facades\Event::dispatch('lead.assigned', $lead);
            }

            return true;
        }

        return false;
    }
}
