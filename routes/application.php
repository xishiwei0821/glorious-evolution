<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('test')->group(function () {
        Route::get('index', [ App\Http\Controllers\Application\V1\Test\IndexController::class, 'index' ]);
    });
});
