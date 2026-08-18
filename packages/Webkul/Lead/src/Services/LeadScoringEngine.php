<?php

namespace Webkul\Lead\Services;

use Carbon\Carbon;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Models\LeadScoreRule;

class LeadScoringEngine
{
    /**
     * Reevaluate the score for a specific lead.
     */
    public function evaluateLead(Lead $lead): void
    {
        $rules = LeadScoreRule::where('is_active', true)->get();
        $totalScore = 0;
        $logsToInsert = [];

        foreach ($rules as $rule) {
            if ($this->evaluateRule($lead, $rule)) {
                $totalScore += $rule->points;
                $logsToInsert[] = [
                    'lead_id' => $lead->id,
                    'rule_id' => $rule->id,
                    'points' => $rule->points,
                    'reason' => "{$rule->name} (".($rule->points > 0 ? '+' : '')."{$rule->points})",
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Cap score at 100
        $totalScore = min(max($totalScore, 0), 100);

        // Wipe old logs and insert new
        $lead->scoreLogs()->delete();
        $lead->scoreLogs()->insert($logsToInsert);

        // Update lead score without triggering events (to prevent loops if we triggered from an event)
        $lead->getConnection()->table('leads')
            ->where('id', $lead->id)
            ->update(['lead_score' => $totalScore]);

        // Update the model instance inline
        $lead->lead_score = $totalScore;
    }

    /**
     * Evaluate a single rule against a lead.
     */
    protected function evaluateRule(Lead $lead, LeadScoreRule $rule): bool
    {
        $conditions = $rule->conditions;

        if ($rule->type === 'attribute') {
            return $this->evaluateAttributeCondition($lead, $conditions);
        }

        if ($rule->type === 'activity') {
            return $this->evaluateActivityCondition($lead, $conditions);
        }

        if ($rule->type === 'recency') {
            return $this->evaluateRecencyCondition($lead, $conditions);
        }

        return false;
    }

    protected function evaluateAttributeCondition(Lead $lead, array $conditions): bool
    {
        $attribute = $conditions['attribute'] ?? null;
        $operator = $conditions['operator'] ?? '==';
        $value = $conditions['value'] ?? null;

        if (! $attribute) {
            return false;
        }

        $leadValue = $lead->{$attribute};

        switch ($operator) {
            case '==':
                return $leadValue == $value;
            case '!=':
                return $leadValue != $value;
            case '>':
                return $leadValue > $value;
            case '<':
                return $leadValue < $value;
            case 'in':
                return is_array($value) && in_array($leadValue, $value);
            case 'not_null':
                return ! is_null($leadValue);
            case 'null':
                return is_null($leadValue);
            default:
                return false;
        }
    }

    protected function evaluateActivityCondition(Lead $lead, array $conditions): bool
    {
        // Example: Responded to WhatsApp
        $type = $conditions['activity_type'] ?? null;

        if (! $type) {
            // Any activity
            return $lead->activities()->count() > 0;
        }

        return $lead->activities()->where('type', $type)->count() > 0;
    }

    protected function evaluateRecencyCondition(Lead $lead, array $conditions): bool
    {
        // Example: No activity for 7 days
        $days = $conditions['days_since_activity'] ?? 7;

        $lastActivity = $lead->last_contacted_at;

        if (! $lastActivity) {
            return true; // No activity ever
        }

        return Carbon::parse($lastActivity)->diffInDays(now()) >= $days;
    }
}
