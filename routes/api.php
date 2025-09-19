<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VideoController;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->group(function () {
    Route::post('/auth/register', 'register');
    Route::post('/auth/login', 'login');
});

Route::get('/courses', [CourseController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('courses', CourseController::class)->except(['index']);
    Route::apiResource('/videos', VideoController::class);

    Route::post('/videos/import', [VideoController::class, 'importVideosFromGumletPlaylist']);

    Route::apiResource('/users', UserController::class);

    Route::controller(UserController::class)->group(function () {
        Route::get('/user', 'me');
        Route::put('/user', 'updateSelf');
        Route::patch('/user', 'updateSelf');
        Route::delete('/user', 'destroySelf');
    });
});
