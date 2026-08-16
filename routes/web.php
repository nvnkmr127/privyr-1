<?php

use App\Http\Controllers\Admin\LeadAnalyticsDashboardController;
use App\Http\Controllers\Admin\LeadCaptureIntegrationController;
use App\Http\Controllers\Admin\LeadDripSequenceController;
use App\Http\Controllers\Admin\LeadRoutingRuleController;
use App\Http\Controllers\ShareableCaptureController;
use App\Http\Controllers\TrackableController;
use Illuminate\Support\Facades\Route;
use Webkul\Admin\Http\Middleware\Bouncer;
use Webkul\Admin\Http\Middleware\Locale;

Route::get('/t/{lead}/{hash}', [TrackableController::class, 'viewDocument'])->name('trackable.document');
Route::post('/t/{lead}/ping', [TrackableController::class, 'ping'])->name('trackable.ping');

Route::get('/c/{token?}', [ShareableCaptureController::class, 'show'])->name('shareable.capture.show');
Route::post('/c/{token?}', [ShareableCaptureController::class, 'store'])->name('shareable.capture.submit');
// All custom admin panels. Guarded by the admin `user` guard (Bouncer) so
// every action requires an authenticated admin — matching the Krayin panel.
Route::middleware([Locale::class, Bouncer::class])
    ->prefix('admin')
    ->group(function () {
        // Lead-capture integrations are tenant-scoped: moldable.workspace resolves
        // the current workspace (tenant) from the authenticated session.
        Route::middleware('moldable.workspace')->prefix('lead-capture/integrations')->group(function () {
            Route::get('', [LeadCaptureIntegrationController::class, 'index'])->name('admin.lead_capture.integrations');
            Route::post('', [LeadCaptureIntegrationController::class, 'store'])->name('admin.lead_capture.integrations.store');
            Route::post('{id}/test', [LeadCaptureIntegrationController::class, 'test'])->name('admin.lead_capture.integrations.test');
            Route::post('{id}/disconnect', [LeadCaptureIntegrationController::class, 'disconnect'])->name('admin.lead_capture.integrations.disconnect');
            Route::post('switch-workspace', [LeadCaptureIntegrationController::class, 'switchWorkspace'])->name('admin.lead_capture.integrations.switch_workspace');
        });

        Route::prefix('settings/drip-sequences')->group(function () {
            Route::get('', [LeadDripSequenceController::class, 'index'])->name('admin.settings.drip_sequences');
            Route::post('', [LeadDripSequenceController::class, 'store'])->name('admin.settings.drip_sequences.store');
            Route::post('{id}', [LeadDripSequenceController::class, 'update'])->name('admin.settings.drip_sequences.update');
            Route::post('{id}/delete', [LeadDripSequenceController::class, 'destroy'])->name('admin.settings.drip_sequences.destroy');
        });

        Route::prefix('settings/lead-routing')->group(function () {
            Route::get('', [LeadRoutingRuleController::class, 'index'])->name('admin.settings.lead_routing');
            Route::post('', [LeadRoutingRuleController::class, 'store'])->name('admin.settings.lead_routing.store');
            Route::post('{id}', [LeadRoutingRuleController::class, 'update'])->name('admin.settings.lead_routing.update');
            Route::post('{id}/delete', [LeadRoutingRuleController::class, 'destroy'])->name('admin.settings.lead_routing.destroy');
        });

        Route::get('analytics/reports', [LeadAnalyticsDashboardController::class, 'index'])->name('admin.analytics.reports');
    });
