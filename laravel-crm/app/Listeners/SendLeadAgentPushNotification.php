<?php

namespace App\Listeners;

use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;

class SendLeadAgentPushNotification
{
    public function __construct(
        protected WhatsAppService $whatsAppService
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

            $alertMessage = "⚡ NEW LEAD ASSIGNED!\n"
                . "Propect: {$prospectName}\n"
                . "Phone: {$phone}\n"
                . "Title: {$lead->title}\n"
                . "View Lead: " . route('admin.leads.view', $lead->id);

            Log::info("Instant New Lead Push Alert for Agent #{$user?->id} ({$agentName}): {$lead->title}");

            // If agent has a phone number set, send WhatsApp push alert directly to agent
            if (!empty($user?->phone)) {
                $this->whatsAppService->send($user->phone, $alertMessage);
            }

            // Record Push Notification log in Activity timeline
            $activity = app(\Webkul\Activity\Repositories\ActivityRepository::class)->create([
                'type' => 'note',
                'comment' => "🔔 Instant Push Notification sent to assigned agent: {$agentName}",
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
