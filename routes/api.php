<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\JobController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DonatorController;
use App\Http\Controllers\Api\DonationController;
use App\Http\Controllers\Api\EventpostController;
use App\Http\Controllers\Api\JobApplicationController;
use App\Http\Controllers\Api\EventRegistrationController;
use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ChapaController;

Route::post('/login', [RegisterController::class, 'login']);
// Route::post('/api/register', [RegisterController::class, 'create']);
Route::post('/register', [RegisterController::class, 'register']);
Route::middleware('auth:api')->get('/protected-route', function () {
    return response()->json(['message' => 'You are authenticated!']);
});

Route::post('/payment/initialize', [ChapaController::class, 'initializePayment']);
Route::get('/payment/verify/{transactionId}', [ChapaController::class, 'verifyPayment']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user/{id}', [UserController::class, 'showUserDetails']);
});
Route::middleware(['auth:sanctum'])->group(function () {


    //Route::put('/eventposts/{id}', [EventpostController::class, 'update']);

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);


    Route::apiResource('donators', DonatorController::class);
    Route::apiResource('event_registrations', EventRegistrationController::class);
    Route::apiResource('job_applications', JobApplicationController::class);
    Route::apiResource('event_post', EventpostController::class);
    Route::apiResource('donation', DonationController::class);
    Route::apiResource('job_post', JobController::class);
});











Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
