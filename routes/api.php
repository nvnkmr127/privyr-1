<?php

use App\Http\Controllers\Api\ContentTemplateController;
use App\Http\Controllers\Api\DeviceTokenController;
use App\Http\Controllers\Api\LeadActivityLoggerController;
use App\Http\Controllers\Api\LeadAnalyticsController;
use App\Http\Controllers\Api\LeadCaptureController;
use App\Http\Controllers\Api\LeadCsvImportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['throttle:60,1'])->group(function () {
    Route::get('/v1/lead-capture/facebook', [LeadCaptureController::class, 'verify']);
    Route::post('/v1/lead-capture', [LeadCaptureController::class, 'capture']);

    // Shared-secret ingestion endpoints: unlike capture() (which authenticates
    // inline per provider), these have no other auth, so gate them explicitly.
    Route::middleware('capture.secret')->group(function () {
        Route::post('/v1/lead-capture/whatsapp-chat', [LeadCaptureController::class, 'importWhatsAppChat']);
        Route::post('/v1/lead-capture/csv-import', [LeadCsvImportController::class, 'import']);
    });

    Route::post('/v1/lead-capture/{provider}', [LeadCaptureController::class, 'capture']);
});
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/v1/lead-analytics', [LeadAnalyticsController::class, 'index']);
    Route::get('/v1/content-templates', [ContentTemplateController::class, 'index']);
    Route::post('/v1/content-templates', [ContentTemplateController::class, 'store']);
    Route::put('/v1/content-templates/{id}', [ContentTemplateController::class, 'update']);
    Route::delete('/v1/content-templates/{id}', [ContentTemplateController::class, 'destroy']);
    Route::post('/v1/content-templates/preview/{lead}', [ContentTemplateController::class, 'preview']);
    Route::post('/v1/leads/{lead}/log-activity', [LeadActivityLoggerController::class, 'logActivity']);

    Route::get('/v1/device-tokens', [DeviceTokenController::class, 'index']);
    Route::post('/v1/device-tokens', [DeviceTokenController::class, 'store']);
    Route::delete('/v1/device-tokens', [DeviceTokenController::class, 'destroy']);
});
