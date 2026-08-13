<?php

use Illuminate\Support\Facades\Route;
use Webkul\Moldable\Http\Controllers\BuilderController;
use Webkul\Moldable\Http\Controllers\ResourceController;
use Webkul\Moldable\Http\Controllers\TemplateController;
use Webkul\Moldable\Http\Controllers\ViewController;
use Webkul\Moldable\Http\Controllers\WorkspaceController;

Route::middleware('auth:api')->prefix('v1/moldable')->group(function () {
    Route::get('/workspaces', [WorkspaceController::class, 'index']);
    Route::post('/workspaces', [WorkspaceController::class, 'store']);

    Route::middleware('moldable.workspace')->group(function () {
        Route::get('/workspace', [WorkspaceController::class, 'show']);
        Route::post('/workspace/members', [WorkspaceController::class, 'addMember']);
        Route::get('/workspace/teams', [WorkspaceController::class, 'teams']);
        Route::post('/workspace/teams', [WorkspaceController::class, 'createTeam']);

        Route::get('/fields', [BuilderController::class, 'fields']);
        Route::post('/fields', [BuilderController::class, 'storeField']);
        Route::put('/fields/{field}', [BuilderController::class, 'updateField']);
        Route::delete('/fields/{field}', [BuilderController::class, 'deleteField']);

        Route::get('/views', [ViewController::class, 'index']);
        Route::post('/views', [ViewController::class, 'store']);
        Route::put('/views/{view}', [ViewController::class, 'update']);
        Route::delete('/views/{view}', [ViewController::class, 'destroy']);

        Route::get('/templates', [TemplateController::class, 'index']);
        Route::post('/templates/{template}/install', [TemplateController::class, 'install']);

        Route::get('/resources', [ResourceController::class, 'index']);
        Route::post('/resources', [ResourceController::class, 'attach']);
        Route::delete('/resources', [ResourceController::class, 'detach']);
    });
});
