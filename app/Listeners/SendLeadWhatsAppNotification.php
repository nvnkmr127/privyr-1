<?php

namespace App\Listeners;

use App\Services\MessageTemplateService;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Webkul\Activity\Repositories\ActivityRepository;

class SendLeadWhatsAppNotification
{
    public function __construct(
        protected MessageTemplateService $templateService,
        protected WhatsAppService $whatsAppService
    ) {}

    /**
     * Handle the event.
     *
     * @param  object  $lead
     * @return void
     */
    public function handle($lead)
    {
        try {
            Log::info("WhatsApp Auto-responder triggered for Lead #{$lead->id}: {$lead->title}");

            $phone = collect($lead->contact_numbers ?? [])->pluck('value')->filter()->first();

            if (! $phone) {
                return;
            }

            $message = $this->templateService->parse(
                config('services.watxio.new_lead_template'),
                $lead
            );

            $sent = $this->whatsAppService->send($phone, $message);

            $activity = app(ActivityRepository::class)->create([
                'type' => 'note',
                'comment' => $sent
                    ? "⚡ Automated WhatsApp auto-responder sent to {$phone}"
                    : "⚡ WhatsApp auto-responder queued for {$phone} (Watxio not configured)",
                'user_id' => $lead->user_id ?? 1,
                'is_done' => 1,
            ]);

            DB::table('lead_activities')->insert([
                'lead_id' => $lead->id,
                'activity_id' => $activity->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('SendLeadWhatsAppNotification error: '.$e->getMessage());
        }
    }
}
