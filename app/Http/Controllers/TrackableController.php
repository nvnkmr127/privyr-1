<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Webkul\Activity\Repositories\ActivityRepository;

class TrackableController extends Controller
{
    public function __construct(
        protected ActivityRepository $activityRepository
    ) {}

    /**
     * Track brochure link open and log event to lead timeline.
     */
    public function viewDocument(Request $request, $lead, $hash)
    {
        $leadId = is_object($lead) ? $lead->id : (int) $lead;
        $leadRecord = is_object($lead) ? $lead : DB::table('leads')->where('id', $leadId)->first();

        if (! $leadRecord) {
            abort(404);
        }

        $expected = md5($leadId.config('app.key'));
        if (! hash_equals($expected, (string) $hash)) {
            abort(404);
        }

        $ip = $request->ip();
        $agent = $request->userAgent();

        // Log activity to Lead Timeline
        $activity = $this->activityRepository->create([
            'type' => 'note',
            'comment' => "📄 🔥 Prospect opened shared brochure/catalogue (IP: {$ip})",
            'user_id' => $leadRecord->user_id,
            'lead_id' => $leadId,
            'is_done' => 1,
        ]);

        // Send instant Engagement Push Alert to assigned agent
        $user = DB::table('users')->where('id', $leadRecord->user_id)->first();
        $prospectName = $leadRecord->person_name ?: 'Prospect';
        $phone = json_decode($leadRecord->contact_numbers ?? '[]', true)[0]['value'] ?? '';

        Log::info("🔥 Engagement alert triggered for Lead #{$leadId} opened by {$prospectName}");

        if ($user && ! empty($user->phone)) {
            $alert = "🔥 ENGAGEMENT ALERT!\n"
                ."{$prospectName} just opened your brochure!\n"
                ."Lead: {$leadRecord->title}\n"
                ."Tap to Call: tel:{$phone}";
            app(WhatsAppService::class)->send($user->phone, $alert);
        }

        $agentName = $user->name ?? 'Sales Representative';
        $appName = config('app.name', 'Moldable Lead CRM');

        return response()->make("
            <!DOCTYPE html>
            <html lang=\"en\">
            <head>
                <meta charset=\"UTF-8\">
                <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
                <title>Exclusively Prepared for {$prospectName} - {$leadRecord->title}</title>
                <style>
                    body { font-family: system-ui, -apple-system, sans-serif; background: #f8fafc; color: #0f172a; margin: 0; padding: 24px; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 90vh; }
                    .card { background: white; border-radius: 16px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); padding: 32px; max-width: 520px; width: 100%; text-align: center; border: 1px solid #e2e8f0; }
                    .badge { background: #dbeafe; color: #1e40af; font-weight: 700; font-size: 12px; text-transform: uppercase; padding: 6px 14px; border-radius: 9999px; display: inline-block; margin-bottom: 16px; }
                    h1 { font-size: 22px; margin-bottom: 8px; color: #1e293b; }
                    .subtitle { color: #64748b; font-size: 14px; line-height: 1.5; margin-bottom: 24px; }
                    .branding { background: #f1f5f9; padding: 12px; border-radius: 8px; font-size: 13px; color: #334155; margin-bottom: 24px; border-left: 4px solid #2563eb; text-align: left; }
                    .btn { background: #2563eb; color: white; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: 600; display: inline-block; transition: background 0.2s; }
                    .btn:hover { background: #1d4ed8; }
                </style>
            </head>
            <body>
                <div class=\"card\">
                    <span class=\"badge\">Exclusively Prepared for {$prospectName}</span>
                    <h1>{$leadRecord->title}</h1>
                    <p class=\"subtitle\">Personalized Proposal & Digital Product Catalogue</p>
                    
                    <div class=\"branding\">
                        <strong>Prepared By:</strong> {$agentName}<br>
                        <strong>Company:</strong> {$appName}
                    </div>

                    <a href=\"https://krayincrm.com/\" target=\"_blank\" class=\"btn\">📄 View Full Interactive PDF</a>
                </div>

                <script>
                    let secondsSpent = 0;
                    setInterval(() => {
                        secondsSpent += 5;
                        if (navigator.sendBeacon) {
                            navigator.sendBeacon('/t/{$leadId}/ping?seconds=' + secondsSpent);
                        }
                    }, 5000);
                </script>
            </body>
            </html>
        ", 200, ['Content-Type' => 'text/html']);
    }

    /**
     * Heartbeat ping endpoint tracking time-spent-viewing per lead.
     */
    public function ping(Request $request, $lead)
    {
        $leadId = is_object($lead) ? $lead->id : (int) $lead;
        $seconds = (int) $request->input('seconds', 5);

        Log::info("⏱️ Time-spent ping for Lead #{$leadId}: {$seconds} seconds spent viewing proposal.");

        return response()->json(['status' => 'success', 'lead_id' => $leadId, 'seconds' => $seconds]);
    }
}
