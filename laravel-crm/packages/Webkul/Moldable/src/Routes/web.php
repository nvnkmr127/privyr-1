<?php

use Illuminate\Support\Facades\Route;
use Webkul\Moldable\Http\Controllers\BuilderViewController;

Route::group(['middleware' => ['web', 'user']], function () {
    Route::get('admin/moldable/builder', [BuilderViewController::class, 'index'])->name('admin.moldable.builder.index');
});
