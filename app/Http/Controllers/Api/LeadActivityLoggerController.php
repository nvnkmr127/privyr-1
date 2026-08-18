<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Webkul\Activity\Repositories\ActivityRepository;

class LeadActivityLoggerController extends Controller
{
    public function __construct(
        protected ActivityRepository $activityRepository
    ) {}

    /**
     * Log online or offline activity (site visit, phone call, WhatsApp chat, meeting).
     */
    public function logActivity(Request $request, $lead)
    {
        $request->validate([
            'type' => 'required|string', // 'call', 'meeting', 'note', 'visit', 'whatsapp'
            'comment' => 'required|string',
            'mode' => 'nullable|string', // 'online', 'offline'
        ]);

        $leadRecord = is_object($lead) ? $lead : DB::table('leads')->where('id', $lead)->first();

        if (! $leadRecord) {
            return response()->json(['status' => 'error', 'message' => 'Lead not found.'], 404);
        }

        $modeTag = $request->input('mode') === 'offline' ? '📍 [Offline Entry] ' : '🌐 [Online Entry] ';

        $activity = $this->activityRepository->create([
            'type' => 'note',
            'comment' => $modeTag.$request->input('comment'),
            'user_id' => $leadRecord->user_id ?? 1,
            'lead_id' => $leadRecord->id,
            'is_done' => 1,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Activity logged successfully.',
            'data' => [
                'activity_id' => $activity->id,
                'lead_id' => $lead->id,
                'mode' => $request->input('mode', 'online'),
            ],
        ], 201);
    }
}
