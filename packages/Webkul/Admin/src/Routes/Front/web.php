<?php

use Illuminate\Support\Facades\Route;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Admin\Http\Controllers\Lead\PublicLeadCaptureController;

/**
 * Home routes.
 */
Route::get('/', [Controller::class, 'redirectToLogin'])->name('krayin.home');

/**
 * Public lead-capture endpoints. These must NOT sit behind admin auth — Meta,
 * Google, Zapier, portals, etc. call them server-to-server. The webhook POST
 * paths are CSRF-exempt in bootstrap/app.php; the QR form is a browser form and
 * keeps CSRF protection.
 */
Route::controller(PublicLeadCaptureController::class)->prefix('api/v1/lead-capture')->group(function () {
    Route::match(['get', 'post'], 'webhook/{token}', 'handleWebhook')->name('api.v1.lead_capture.webhook');
    Route::match(['get', 'post'], 'indiamart', 'handleIndiaMART')->name('api.v1.lead_capture.indiamart');
    Route::match(['get', 'post'], 'justdial', 'handleJustDial')->name('api.v1.lead_capture.justdial');
    Route::match(['get', 'post'], 'realestate', 'handleRealEstate')->name('api.v1.lead_capture.realestate');
});

Route::controller(PublicLeadCaptureController::class)->prefix('lead-capture')->group(function () {
    Route::get('qr/{token}', 'qrForm')->name('public.lead_capture.qr_form');
    Route::post('qr/{token}', 'qrStore')->name('public.lead_capture.qr_store');

    // Embeddable loader script: external sites include this and it injects the
    // hosted form as an iframe into [data-lead-capture="<token>"].
    Route::get('embed/{token}.js', 'embedJs')->name('public.lead_capture.embed_js');
});
