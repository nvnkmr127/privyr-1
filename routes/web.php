<?php

use App\Http\Controllers\Admin\LeadAnalyticsDashboardController;
use App\Http\Controllers\Admin\LeadCaptureIntegrationController;
use App\Http\Controllers\Admin\LeadDripSequenceController;
use App\Http\Controllers\Admin\LeadRoutingRuleController;
use App\Http\Controllers\ShareableCaptureController;
use App\Http\Controllers\TrackableController;
use Illuminate\Support\Facades\Route;

Route::get('/t/{lead}/{hash}', [TrackableController::class, 'viewDocument'])->name('trackable.document');
Route::post('/t/{lead}/ping', [TrackableController::class, 'ping'])->name('trackable.ping');

Route::get('/c/{token?}', [ShareableCaptureController::class, 'show'])->name('shareable.capture.show');
Route::post('/c/{token?}', [ShareableCaptureController::class, 'store'])->name('shareable.capture.submit');
Route::get('/admin/lead-capture/integrations', [LeadCaptureIntegrationController::class, 'index'])->name('admin.lead_capture.integrations');
Route::get('/admin/settings/drip-sequences', [LeadDripSequenceController::class, 'index'])->name('admin.settings.drip_sequences');
Route::post('/admin/settings/drip-sequences', [LeadDripSequenceController::class, 'store'])->name('admin.settings.drip_sequences.store');
Route::post('/admin/settings/drip-sequences/{id}', [LeadDripSequenceController::class, 'update'])->name('admin.settings.drip_sequences.update');
Route::post('/admin/settings/drip-sequences/{id}/delete', [LeadDripSequenceController::class, 'destroy'])->name('admin.settings.drip_sequences.destroy');
Route::get('/admin/settings/lead-routing', [LeadRoutingRuleController::class, 'index'])->name('admin.settings.lead_routing');
Route::post('/admin/settings/lead-routing', [LeadRoutingRuleController::class, 'store'])->name('admin.settings.lead_routing.store');
Route::post('/admin/settings/lead-routing/{id}', [LeadRoutingRuleController::class, 'update'])->name('admin.settings.lead_routing.update');
Route::post('/admin/settings/lead-routing/{id}/delete', [LeadRoutingRuleController::class, 'destroy'])->name('admin.settings.lead_routing.destroy');
Route::get('/admin/analytics/reports', [LeadAnalyticsDashboardController::class, 'index'])->name('admin.analytics.reports');
