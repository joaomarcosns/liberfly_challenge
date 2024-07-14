<?php

use App\Http\Controllers\Api\v1\Auth\AuthController;
use App\Http\Controllers\Api\v1\PostsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::controller(AuthController::class)->prefix('auth')->name('auth.')->group(function () {
        Route::post('register', 'register')->name('register');
        Route::post('login', 'login')->name('login');
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::controller(PostsController::class)->name('posts.')->prefix('posts')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::get('/{post_id}', 'show')->name('show');
            Route::put('/{post_id}', 'update')->name('update');
            Route::patch('/{post_id}/published', 'published')->name('published');
            Route::patch('/{post_id}/archive', 'archive')->name('archive');
        });

        Route::get('/user', function (Request $request) {
            return $request->user();
        });
    });
});
