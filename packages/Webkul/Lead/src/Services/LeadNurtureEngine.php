<?php

namespace Webkul\Lead\Services;

use Carbon\Carbon;
use Webkul\Activity\Models\Activity;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Models\LeadNurtureEnrollment;
use Webkul\Lead\Models\LeadNurtureStep;

class LeadNurtureEngine
{
    /**
     * Process all active enrollments that are ready to resume.
     */
    public function processActiveEnrollments()
    {
        $enrollments = LeadNurtureEnrollment::with(['lead', 'sequence', 'currentStep'])
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('resume_at')
                      ->orWhere('resume_at', '<=', Carbon::now());
            })
            ->get();

        foreach ($enrollments as $enrollment) {
            $this->processEnrollment($enrollment);
        }
    }

    /**
     * Process a single enrollment.
     */
    public function processEnrollment(LeadNurtureEnrollment $enrollment)
    {
        if (! $enrollment->lead || ! $enrollment->sequence) {
            $enrollment->update(['status' => 'failed']);
            return;
        }

        // Check Stop Condition
        if ($this->meetsStopCondition($enrollment->lead, $enrollment->sequence->stop_condition)) {
            $enrollment->update(['status' => 'stopped']);
            return;
        }

        while ($enrollment->status === 'active') {
            $step = $enrollment->currentStep;

            if (! $step) {
                // No current step means completion
                $enrollment->update(['status' => 'completed']);
                break;
            }

            try {
                $nextStepId = $this->executeStep($enrollment, $step);

                if ($nextStepId === false) {
                    // Step is pausing execution (like a wait step)
                    break;
                }

                $enrollment->update([
                    'current_step_id' => $nextStepId,
                    'resume_at' => null, // clear resume_at when moving to next step
                ]);

                $enrollment->refresh();

                // If next step is null, it completed the sequence
                if (! $nextStepId) {
                    $enrollment->update(['status' => 'completed']);
                    break;
                }

            } catch (\Exception $e) {
                $enrollment->update([
                    'status' => 'failed',
                    'context' => array_merge($enrollment->context ?? [], ['error' => $e->getMessage()]),
                ]);
                break;
            }
        }
    }

    /**
     * Execute a specific step and return the next step ID (or false if pausing).
     */
    protected function executeStep(LeadNurtureEnrollment $enrollment, LeadNurtureStep $step)
    {
        switch ($step->type) {
            case 'message':
                return $this->executeMessageStep($enrollment, $step);
            case 'wait':
                return $this->executeWaitStep($enrollment, $step);
            case 'condition':
                return $this->executeConditionStep($enrollment, $step);
            default:
                throw new \Exception("Unknown step type: {$step->type}");
        }
    }

    protected function executeMessageStep(LeadNurtureEnrollment $enrollment, LeadNurtureStep $step)
    {
        $config = $step->config ?? [];
        $channel = $config['channel'] ?? 'email';
        $content = $config['content'] ?? '';

        // Log to timeline
        Activity::create([
            'title' => "Nurture Message Sent ({$channel})",
            'type' => 'system',
            'comment' => $content,
            'is_done' => 1,
            'lead_id' => $enrollment->lead_id,
            'user_id' => $enrollment->lead->user_id,
            'schedule_from' => Carbon::now(),
            'schedule_to' => Carbon::now(),
        ]);

        return $step->next_step_id;
    }

    protected function executeWaitStep(LeadNurtureEnrollment $enrollment, LeadNurtureStep $step)
    {
        // If we are already waiting, and processEnrollment was called, it means resume_at has passed
        if ($enrollment->resume_at && $enrollment->resume_at <= Carbon::now()) {
            return $step->next_step_id;
        }

        $config = $step->config ?? [];
        $days = $config['days'] ?? 0;
        $hours = $config['hours'] ?? 0;

        $resumeAt = Carbon::now()->addDays($days)->addHours($hours);

        $enrollment->update(['resume_at' => $resumeAt]);

        return false; // False means execution pauses here
    }

    protected function executeConditionStep(LeadNurtureEnrollment $enrollment, LeadNurtureStep $step)
    {
        $config = $step->config ?? [];
        $conditionType = $config['type'] ?? '';

        $isMet = false;

        if ($conditionType === 'engaged') {
            // Example logic: Lead has replied if there is an activity created by lead/customer, or last_contacted_at is recent
            $isMet = ! $enrollment->lead->is_unread && $enrollment->lead->last_contacted_at;
        }

        return $isMet ? $step->next_step_id : $step->alt_next_step_id;
    }

    protected function meetsStopCondition(Lead $lead, ?array $stopCondition): bool
    {
        if (! $stopCondition) {
            return false;
        }

        $type = $stopCondition['type'] ?? '';
        if ($type === 'replied' && ! $lead->is_unread) {
            return true;
        }

        if ($type === 'stage_won' && $lead->stage && $lead->stage->code === 'won') {
            return true;
        }

        return false;
    }
}
