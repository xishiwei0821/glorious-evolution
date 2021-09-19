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
    return view('welcome');
});

Route::prefix('wx_public')->group(function () {
    Route::get('/wx_access', [ App\Http\Controllers\Wxpublic\IndexController::class, 'wx_access' ]);
    Route::get('/menus', [ App\Http\Controllers\Wxpublic\IndexController::class, 'menus' ]);
});
