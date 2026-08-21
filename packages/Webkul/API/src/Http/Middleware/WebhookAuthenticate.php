<?php

namespace Webkul\API\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class WebhookAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $signature = $request->header('X-Signature');
        $timestamp = $request->header('X-Timestamp');

        if (! $signature || ! $timestamp) {
            return response()->json(['error' => 'Missing webhook signature or timestamp.'], 401);
        }

        // Prevent replay attacks by checking timestamp window (e.g., 5 minutes)
        try {
            $requestTime = Carbon::createFromTimestamp($timestamp);
            if (Carbon::now()->diffInMinutes($requestTime) > 5) {
                return response()->json(['error' => 'Webhook request expired.'], 401);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid timestamp format.'], 401);
        }

        $secret = config('services.webhook.secret');

        if (! $secret) {
            return response()->json(['error' => 'Webhook secret not configured on server.'], 500);
        }

        $payload = $timestamp.'.'.$request->getContent();
        $expectedSignature = hash_hmac('sha256', $payload, $secret);

        if (! hash_equals($expectedSignature, $signature)) {
            return response()->json(['error' => 'Invalid webhook signature.'], 401);
        }

        $eventId = $request->header('X-Event-ID') ?? $signature;
        $cacheKey = 'webhook_replay_'.$eventId;

        if (Cache::has($cacheKey)) {
            return response()->json(['error' => 'Webhook request already processed.'], 401);
        }
        Cache::put($cacheKey, true, now()->addMinutes(5));

        return $next($request);
    }
}
