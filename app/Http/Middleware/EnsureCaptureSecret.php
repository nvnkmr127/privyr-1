<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards the shared-secret lead-capture endpoints (CSV import, WhatsApp chat
 * import) that are otherwise only throttled. The secret is provided via the
 * X-Capture-Secret header or ?key= query param and compared in constant time.
 *
 * This is the same gate LeadCaptureController::capture() applies inline for
 * generic providers, extracted so sibling ingestion endpoints share it.
 */
class EnsureCaptureSecret
{
    public function handle(Request $request, Closure $next): Response
    {
        $secret = config('services.lead_capture.secret');

        if (! $secret) {
            return response()->json([
                'status' => 'error',
                'message' => 'Capture secret not configured on server.',
            ], 401);
        }

        $provided = $request->header('X-Capture-Secret') ?? $request->query('key');

        if (! is_string($provided) || ! hash_equals((string) $secret, $provided)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized.',
            ], 401);
        }

        return $next($request);
    }
}
