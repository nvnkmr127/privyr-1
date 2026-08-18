<?php

namespace Webkul\Lead\Services;

use Illuminate\Support\Facades\DB;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Models\LeadAssignmentRule;

class LeadAssignmentService
{
    public function assignLead(Lead $lead): bool
    {
        // Get active rules ordered by sort_order
        $rules = LeadAssignmentRule::with(['conditions', 'users'])->where('status', true)->orderBy('sort_order', 'asc')->get();

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

            if (!$this->evaluateCondition($condition->operator, $leadValue, $condition->value)) {
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
                return strtolower((string)$leadValue) === strtolower((string)$ruleValue);
            case 'contains':
                return strpos(strtolower((string)$leadValue), strtolower((string)$ruleValue)) !== false;
            case '>':
                return (float)$leadValue > (float)$ruleValue;
            case '<':
                return (float)$leadValue < (float)$ruleValue;
            case '!=':
            case 'not_equals':
                return strtolower((string)$leadValue) !== strtolower((string)$ruleValue);
        }

        return false;
    }

    protected function executeRule(LeadAssignmentRule $rule, Lead $lead): bool
    {
        if ($rule->users->isEmpty()) {
            return false;
        }

        $assignedUserId = null;

        if ($rule->type === 'direct') {
            $assignedUserId = $rule->users->first()->id;
        } elseif ($rule->type === 'round_robin') {
            // Find the user who was assigned least recently
            $user = $rule->users()->orderByPivot('last_assigned_at', 'asc')->first();
            if ($user) {
                $assignedUserId = $user->id;
                $rule->users()->updateExistingPivot($user->id, ['last_assigned_at' => now()]);
            }
        } elseif ($rule->type === 'weighted') {
            // Very simple weighted RR: not perfect but adequate for MVP.
            // A better way is to track assignments over a window. 
            // Here we use weight as a simple score. We can just pick the user with the lowest `last_assigned_at` weighted by weight.
            // For now, let's just do random weighted or simple modulo. We'll do random based on weight distribution.
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

        if ($assignedUserId) {
            $previousOwner = $lead->user_id;
            
            // Bypass updating Lead updated_at timestamp to avoid infinite loops if triggered via observers
            DB::table('leads')->where('id', $lead->id)->update(['user_id' => $assignedUserId]);
            $lead->user_id = $assignedUserId;
            
            // Log history
            app(\Webkul\Lead\Repositories\LeadAssignmentRepository::class)->create([
                'lead_id' => $lead->id,
                'assigned_to' => $assignedUserId,
                'assigned_by' => auth()->check() ? auth()->id() : null,
                'previous_owner' => $previousOwner,
                'reason' => "Assigned via Rule: {$rule->name}"
            ]);

            return true;
        }

        return false;
    }
}
