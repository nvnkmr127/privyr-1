<?php

use Illuminate\Support\Facades\Route;

test('field builder ui web route is registered', function () {
    expect(Route::has('admin.moldable.builder.index'))->toBeTrue();
});
