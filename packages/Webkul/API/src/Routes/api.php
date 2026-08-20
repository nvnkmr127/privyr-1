<?php

use Illuminate\Support\Facades\Route;
use Webkul\API\Http\Controllers\LeadIngestionController;
use Webkul\API\Http\Controllers\WebhookIngestionController;

Route::middleware(['throttle:60,1'])->group(function () {
    // API Authentication route
    Route::middleware(['webkul.api.auth:lead.ingest'])->group(function () {
        Route::post('leads', [LeadIngestionController::class, 'store']);
    });

    // Webhook Authentication route
    Route::middleware(['webkul.webhook.auth'])->group(function () {
        Route::post('webhooks/leads', [WebhookIngestionController::class, 'store']);
    });
});
