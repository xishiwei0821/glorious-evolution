<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect(route('web.index'));
});

Route::prefix('web')->group(function () {
    Route::get('/index', [ App\Http\Controllers\Web\IndexController::class, 'index' ])->name('web.index');
    // Route::get('/test', [ App\Http\Controllers\Web\TestController::class, 'index' ]);
});

Route::prefix('wxPublic')->group(function () {
    Route::get('/wxAccess', [ App\Http\Controllers\Wxpublic\IndexController::class, 'wx_access' ]);
});
