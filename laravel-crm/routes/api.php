<?php

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

Route::get('/v1/lead-capture/facebook', [App\Http\Controllers\Api\LeadCaptureController::class, 'verify']);
Route::post('/v1/lead-capture', [App\Http\Controllers\Api\LeadCaptureController::class, 'capture']);
Route::post('/v1/lead-capture/whatsapp-chat', [App\Http\Controllers\Api\LeadCaptureController::class, 'importWhatsAppChat']);
Route::post('/v1/lead-capture/csv-import', [App\Http\Controllers\Api\LeadCsvImportController::class, 'import']);
Route::post('/v1/lead-capture/{provider}', [App\Http\Controllers\Api\LeadCaptureController::class, 'capture']);
Route::get('/v1/lead-capture/meta-capi-test', function () {
    return response()->json(app(\App\Services\MetaConversionsApiService::class)->testConversion());
});
Route::get('/v1/lead-analytics/meta-quality', function () {
    return response()->json(app(\App\Services\MetaConversionsApiService::class)->getEventQualityMetrics());
});
Route::get('/v1/lead-analytics', [App\Http\Controllers\Api\LeadAnalyticsController::class, 'index']);
Route::get('/v1/content-templates', [App\Http\Controllers\Api\ContentTemplateController::class, 'index']);
Route::post('/v1/content-templates', [App\Http\Controllers\Api\ContentTemplateController::class, 'store']);
Route::put('/v1/content-templates/{id}', [App\Http\Controllers\Api\ContentTemplateController::class, 'update']);
Route::delete('/v1/content-templates/{id}', [App\Http\Controllers\Api\ContentTemplateController::class, 'destroy']);
Route::post('/v1/content-templates/preview/{lead}', [App\Http\Controllers\Api\ContentTemplateController::class, 'preview']);
Route::post('/v1/leads/{lead}/log-activity', [App\Http\Controllers\Api\LeadActivityLoggerController::class, 'logActivity']);







