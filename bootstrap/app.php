<?php

use App\Http\Middleware\EnsureCaptureSecret;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Webkul\Installer\Http\Middleware\CanInstall;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(CanInstall::class);

        $middleware->alias([
            'capture.secret' => EnsureCaptureSecret::class,
        ]);

        $middleware->encryptCookies(except: [
            'dark_mode',
            'sidebar_collapsed',
        ]);

        $middleware->validateCsrfTokens(except: [
            'admin/mail/inbound-parse',
            'admin/web-forms/forms/*',
            't/*/ping',
            // Server-to-server lead webhooks (Meta, Google, Zapier, portals).
            // The QR browser form (lead-capture/qr/*) is intentionally not listed.
            'api/v1/lead-capture/webhook/*',
            'api/v1/lead-capture/indiamart',
            'api/v1/lead-capture/justdial',
            'api/v1/lead-capture/realestate',
        ]);

        $middleware->api(prepend: [
            EnsureFrontendRequestsAreStateful::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
