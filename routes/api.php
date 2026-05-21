<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\CandidateProfileController;
use App\Http\Controllers\EmployerProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// ── Public auth routes ────────────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('/login',           [AuthController::class, 'login']);
    Route::post('/register',        [AuthController::class, 'register']);
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink']);
    Route::post('/reset-password',  [ForgotPasswordController::class, 'resetPassword']);
});

// ── Protected routes (requires Sanctum token) ─────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',      [AuthController::class, 'me']);

    // Candidate profile routes
    Route::prefix('candidate')->group(function () {
        Route::get('/profile', [CandidateProfileController::class, 'show']);
        Route::put('/profile', [CandidateProfileController::class, 'update']);
        Route::post('/resume', [CandidateProfileController::class, 'uploadResume']);
        Route::delete('/resume',[CandidateProfileController::class, 'deleteResume']);
    });

    // Employer profile routes
    Route::prefix('employer')->group(function () {
        Route::get('/profile',[EmployerProfileController::class, 'show']);
        Route::put('/profile', [EmployerProfileController::class, 'update']);
        Route::get('/candidates',[EmployerProfileController::class, 'browseCandidates']);
    });
});