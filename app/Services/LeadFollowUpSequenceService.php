<?php

namespace App\Services;

use App\Models\DripSequenceStep;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LeadFollowUpSequenceService
{
    public function __construct(
        protected MessageTemplateService $templateService,
        protected WhatsAppService $whatsAppService
    ) {}

    /**
     * Active drip steps, keyed by step id so processing/dedup stays stable.
     * Managers edit these via the drip_sequence_steps table.
     */
    public function getSequenceSteps(): array
    {
        return DripSequenceStep::where('is_active', true)
            ->orderBy('day_offset')
            ->get()
            ->mapWithKeys(fn ($step) => [$step->id => [
                'day_offset' => $step->day_offset,
                'name' => $step->name,
                'template' => $step->content,
            ]])
            ->all();
    }

    /**
     * Process pending sequence drip steps for all active leads.
     */
    public function processPendingSteps(): int
    {
        $steps = $this->getSequenceSteps();
        $leads = DB::table('leads')->where('status', 1)->get(); // Active open leads
        $processedCount = 0;

        foreach ($leads as $lead) {
            $createdDaysAgo = (int) Carbon::parse($lead->created_at)->diffInDays(Carbon::now());

            foreach ($steps as $stepId => $step) {
                if ($createdDaysAgo >= $step['day_offset']) {
                    $alreadyProcessed = DB::table('lead_activities')
                        ->join('activities', 'activities.id', '=', 'lead_activities.activity_id')
                        ->where('lead_activities.lead_id', $lead->id)
                        ->where('activities.comment', 'like', "%[Sequence Step #{$stepId}]%")
                        ->exists();

                    if (!$alreadyProcessed) {
                        $this->dispatchStep($lead, $stepId, $step);
                        $processedCount++;
                    }
                }
            }
        }

        return $processedCount;
    }

    /**
     * Dispatch a single sequence step for a lead.
     */
    protected function dispatchStep($lead, int $stepId, array $step): void
    {
        $hash = md5($lead->id . config('app.key'));
        $brochureLink = route('trackable.document', ['lead' => $lead->id, 'hash' => $hash]);

        $message = $this->templateService->parse($step['template'], $lead);
        $message = str_replace('{brochure_link}', $brochureLink, $message);

        $person = DB::table('persons')->where('id', $lead->person_id)->first();
        $phone = json_decode($person->contact_numbers ?? '[]', true)[0]['value'] ?? null;

        Log::info("Dispatching Sequence Step #{$stepId} ('{$step['name']}') for Lead #{$lead->id}");

        if ($phone) {
            $this->whatsAppService->send($phone, $message);
        }

        $activity = app(\Webkul\Activity\Repositories\ActivityRepository::class)->create([
            'type' => 'note',
            'comment' => "🤖 [Sequence Step #{$stepId}] Sent: {$step['name']}\nMessage: {$message}",
            'user_id' => $lead->user_id ?? 1,
            'is_done' => 1,
        ]);

        DB::table('lead_activities')->insert([
            'lead_id' => $lead->id,
            'activity_id' => $activity->id,
        ]);
    }
}
