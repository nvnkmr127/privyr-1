<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceTokenController extends Controller
{
    /**
     * List the authenticated agent's registered devices.
     */
    public function index(Request $request): JsonResponse
    {
        $tokens = DeviceToken::where('user_id', $request->user()->id)
            ->orderByDesc('last_used_at')
            ->orderByDesc('created_at')
            ->get(['id', 'platform', 'device_name', 'last_used_at', 'created_at']);

        return response()->json([
            'status' => 'success',
            'data' => $tokens,
        ]);
    }

    /**
     * Register (or refresh) the push token for the agent's device.
     *
     * A token is globally unique, so re-registering the same device simply
     * re-points it at the current agent — handy on shared phones.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => 'required|string|max:255',
            'platform' => 'nullable|in:android,ios,web',
            'device_name' => 'nullable|string|max:255',
        ]);

        // last_used_at intentionally left untouched here — it tracks successful
        // push delivery (stamped in PushNotificationService), not registration.
        $deviceToken = DeviceToken::updateOrCreate(
            ['token' => $data['token']],
            [
                'user_id' => $request->user()->id,
                'platform' => $data['platform'] ?? 'android',
                'device_name' => $data['device_name'] ?? null,
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Device registered for instant new-lead alerts.',
            'data' => ['id' => $deviceToken->id],
        ], 201);
    }

    /**
     * Unregister a device (e.g. on logout) so it stops receiving alerts.
     */
    public function destroy(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => 'required|string|max:255',
        ]);

        DeviceToken::where('user_id', $request->user()->id)
            ->where('token', $data['token'])
            ->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Device unregistered.',
        ]);
    }
}
