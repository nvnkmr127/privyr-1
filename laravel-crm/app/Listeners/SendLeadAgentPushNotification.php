<?php

namespace App\Listeners;

use App\Services\PushNotificationService;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendLeadAgentPushNotification
{
    public function __construct(
        protected WhatsAppService $whatsAppService,
        protected PushNotificationService $pushService
    ) {}

    /**
     * Send instant push notification to assigned agent when lead is created.
     *
     * @param object $lead
     * @return void
     */
    public function handle($lead)
    {
        try {
            $user = $lead->user;
            $agentName = $user->name ?? 'Agent';
            $prospectName = $lead->person?->name ?? 'New Prospect';
            $phone = collect($lead->person?->contact_numbers ?? [])->pluck('value')->filter()->first() ?? 'N/A';

            $leadUrl = route('admin.leads.view', $lead->id);

            $alertMessage = "⚡ NEW LEAD ASSIGNED!\n"
                . "Prospect: {$prospectName}\n"
                . "Phone: {$phone}\n"
                . "Title: {$lead->title}\n"
                . "View Lead: " . $leadUrl;

            Log::info("Instant New Lead Push Alert for Agent #{$user?->id} ({$agentName}): {$lead->title}");

            // Real push straight to the agent's registered phones — the headline promise.
            $pushed = $this->pushService->sendToUser(
                $user,
                '⚡ New Lead Assigned',
                "{$prospectName} · {$phone} · {$lead->title}",
                [
                    'lead_id' => (string) $lead->id,
                    'type' => 'new_lead',
                    'url' => $leadUrl,
                ]
            );

            // Fallback: if the agent has a phone but no registered device, buzz
            // them over WhatsApp so the alert still lands.
            if (! $pushed && ! empty($user?->phone)) {
                $this->whatsAppService->send($user->phone, $alertMessage);
            }

            // Record Push Notification log in Activity timeline
            $activity = app(\Webkul\Activity\Repositories\ActivityRepository::class)->create([
                'type' => 'note',
                'comment' => $pushed
                    ? "🔔 Instant push notification delivered to {$agentName}'s device(s)"
                    : "🔔 New-lead alert dispatched to assigned agent: {$agentName}",
                'user_id' => $lead->user_id ?? 1,
                'is_done' => 1,
            ]);

            DB::table('lead_activities')->insert([
                'lead_id' => $lead->id,
                'activity_id' => $activity->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('SendLeadAgentPushNotification error: ' . $e->getMessage());
        }
    }
}
