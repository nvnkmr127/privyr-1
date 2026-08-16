<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShareableCaptureController;
use App\Http\Controllers\TrackableController;

Route::get('/t/{lead}/{hash}', [TrackableController::class, 'viewDocument'])->name('trackable.document');
Route::post('/t/{lead}/ping', [TrackableController::class, 'ping'])->name('trackable.ping');

Route::get('/c/{token?}', [ShareableCaptureController::class, 'show'])->name('shareable.capture.show');
Route::post('/c/{token?}', [ShareableCaptureController::class, 'store'])->name('shareable.capture.submit');
Route::get('/admin/lead-capture/integrations', [App\Http\Controllers\Admin\LeadCaptureIntegrationController::class, 'index'])->name('admin.lead_capture.integrations');
Route::get('/admin/settings/drip-sequences', [App\Http\Controllers\Admin\LeadDripSequenceController::class, 'index'])->name('admin.settings.drip_sequences');
Route::post('/admin/settings/drip-sequences', [App\Http\Controllers\Admin\LeadDripSequenceController::class, 'store'])->name('admin.settings.drip_sequences.store');
Route::post('/admin/settings/drip-sequences/{id}', [App\Http\Controllers\Admin\LeadDripSequenceController::class, 'update'])->name('admin.settings.drip_sequences.update');
Route::post('/admin/settings/drip-sequences/{id}/delete', [App\Http\Controllers\Admin\LeadDripSequenceController::class, 'destroy'])->name('admin.settings.drip_sequences.destroy');
Route::get('/admin/settings/lead-routing', [App\Http\Controllers\Admin\LeadRoutingRuleController::class, 'index'])->name('admin.settings.lead_routing');
Route::post('/admin/settings/lead-routing', [App\Http\Controllers\Admin\LeadRoutingRuleController::class, 'store'])->name('admin.settings.lead_routing.store');
Route::post('/admin/settings/lead-routing/{id}', [App\Http\Controllers\Admin\LeadRoutingRuleController::class, 'update'])->name('admin.settings.lead_routing.update');
Route::post('/admin/settings/lead-routing/{id}/delete', [App\Http\Controllers\Admin\LeadRoutingRuleController::class, 'destroy'])->name('admin.settings.lead_routing.destroy');
Route::get('/admin/analytics/reports', [App\Http\Controllers\Admin\LeadAnalyticsDashboardController::class, 'index'])->name('admin.analytics.reports');





