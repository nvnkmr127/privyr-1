<?php

namespace App\Listeners;

use App\Services\PushNotificationService;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Webkul\Activity\Repositories\ActivityRepository;

class SendLeadAgentPushNotification
{
    public function __construct(
        protected WhatsAppService $whatsAppService,
        protected PushNotificationService $pushService
    ) {}

    /**
     * Send instant push notification to assigned agent when lead is created.
     *
     * @param  object  $lead
     * @return void
     */
    public function handle($lead)
    {
        Log::info("SendLeadAgentPushNotification fired for lead: " . $lead->id);
        try {
            $user = $lead->user;
            $agentName = $user->name ?? 'Agent';
            $prospectName = $lead->person_name ?? 'New Prospect';
            $phone = collect($lead->contact_numbers ?? [])->pluck('value')->filter()->first() ?? 'N/A';

            $leadUrl = route('admin.leads.view', $lead->id);

            $alertMessage = "⚡ NEW LEAD ASSIGNED!\n"
                ."Prospect: {$prospectName}\n"
                ."Phone: {$phone}\n"
                ."Title: {$lead->title}\n"
                .'View Lead: '.$leadUrl;

            Log::info("Instant New Lead Push Alert for Agent #{$user?->id} ({$agentName}): {$lead->title}");

            // Real push straight to the agent's registered phones — the headline promise.
            $pushResult = $this->pushService->sendToUser(
                $user,
                '⚡ New Lead Assigned',
                "{$prospectName} · {$phone} · {$lead->title}",
                [
                    'lead_id' => (string) $lead->id,
                    'type' => 'new_lead',
                    'url' => $leadUrl,
                ]
            );
            $pushed = $pushResult['sent'] ?? false;
            $pushReason = $pushResult['reason'] ?? null;

            // Fallback: if push didn't land and the agent has a phone, buzz them
            // over WhatsApp so the alert still reaches them.
            $whatsappSent = false;
            if (! $pushed && ! empty($user?->phone)) {
                $whatsappSent = $this->whatsAppService->send($user->phone, $alertMessage);
            }

            // Reflect the real outcome of the fallback chain in the timeline.
            if ($pushed) {
                $comment = "🔔 Instant push notification delivered to {$agentName}'s device(s)";
            } elseif ($whatsappSent) {
                if ($pushReason === 'no_tokens') {
                    $comment = "🔔 Push skipped (no registered devices) — new-lead alert sent to {$agentName} via WhatsApp";
                } else {
                    $comment = "🔔 Push failed ({$pushReason}) — new-lead alert sent to {$agentName} via WhatsApp";
                }
            } else {
                $comment = "🔔 New-lead alert not delivered to {$agentName} (no registered device or WhatsApp channel)";
            }

            // Record Push Notification log in Activity timeline
            $activity = app(ActivityRepository::class)->create([
                'type' => 'note',
                'comment' => $comment,
                'user_id' => $lead->user_id ?? 1,
                'is_done' => 1,
            ]);

            DB::table('lead_activities')->insert([
                'lead_id' => $lead->id,
                'activity_id' => $activity->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('SendLeadAgentPushNotification error: '.$e->getMessage());
        }
    }
}
