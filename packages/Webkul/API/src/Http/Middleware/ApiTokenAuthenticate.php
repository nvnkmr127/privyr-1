<?php

namespace Webkul\API\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Webkul\API\Repositories\ApiCredentialRepository;
use Carbon\Carbon;

class ApiTokenAuthenticate
{
    protected $apiCredentialRepository;

    public function __construct(ApiCredentialRepository $apiCredentialRepository)
    {
        $this->apiCredentialRepository = $apiCredentialRepository;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $permission
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $permission = null)
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $hashedToken = hash('sha256', $token);

        $credential = $this->apiCredentialRepository->findOneByField('token_hash', $hashedToken);

        if (! $credential) {
            return response()->json(['error' => 'Invalid or expired token.'], 401);
        }

        if (! $credential->status) {
            return response()->json(['error' => 'Token has been disabled.'], 403);
        }

        if ($credential->expires_at && $credential->expires_at->isPast()) {
            return response()->json(['error' => 'Token has expired.'], 401);
        }

        if ($permission && ! $credential->hasPermission($permission)) {
            return response()->json(['error' => 'Unauthorized action.'], 403);
        }

        // Update last used at timestamp (doing this async or directly)
        $credential->update(['last_used_at' => Carbon::now()]);

        // Add credential to request for later use
        $request->attributes->set('api_credential', $credential);

        return $next($request);
    }
}
