<?php

use Illuminate\Support\Facades\Route;
use Webkul\Admin\Http\Controllers\FollowUpController;

Route::group(['middleware' => ['admin'], 'prefix' => config('app.admin_url')], function () {
    Route::controller(FollowUpController::class)->prefix('follow-ups')->group(function () {
        Route::get('', 'index')->name('admin.follow_ups.index');
        Route::post('', 'store')->name('admin.follow_ups.store');
        Route::put('{id}', 'update')->name('admin.follow_ups.update');
        Route::post('{id}/complete', 'complete')->name('admin.follow_ups.complete');
        Route::post('{id}/cancel', 'cancel')->name('admin.follow_ups.cancel');
        Route::post('{id}/reschedule', 'reschedule')->name('admin.follow_ups.reschedule');
        Route::delete('{id}', 'destroy')->name('admin.follow_ups.destroy');
    });
});
