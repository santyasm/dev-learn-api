<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\VideoProgressController;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->group(function () {
    Route::post('/auth/register', 'register');
    Route::post('/auth/login', 'login');
});

Route::get('/courses', [CourseController::class, 'index'])->middleware('auth.optional');


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

    Route::apiResource('/enrollments', EnrollmentController::class);
    Route::get('/user/enrollments', [EnrollmentController::class, "getMyEnrollments"]);


    Route::post('/videos/{enrollment}/{video}/complete', [VideoProgressController::class, 'store'])
        ->name('videos.complete.store');
    Route::delete('/videos/{enrollment}/{video}/complete', [VideoProgressController::class, 'destroy'])
        ->name('videos.complete.destroy');
    Route::get('/enrollments/{enrollment}/completed-videos', [VideoProgressController::class, 'index'])
        ->name('videos.complete.index');
});
