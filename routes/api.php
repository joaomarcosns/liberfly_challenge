<?php

use App\Http\Controllers\Api\v1\Auth\AuthController;
use App\Http\Controllers\UsersController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::controller(AuthController::class)->prefix('auth')->name('auth.')->group(function () {
        Route::post('register', 'register')->name("register");
        Route::post('login', 'login')->name('login');
    });

    Route::get('/user', function (Request $request) {
        return $request->user();
    })->middleware('auth:sanctum');
});
