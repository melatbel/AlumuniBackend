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
// use App\Http\Controllers\AdminController;

// Public Routes
// Route::post('/login', [RegisterController::class, 'login']);
Route::post('/login', [RegisterController::class, 'login'])->name('login');
Route::post('/register', [RegisterController::class, 'register']);


// Protected Routes (Require Authentication)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/protected-route', function () {
        return response()->json(['message' => 'You are authenticated!']);
    });

    // Route::post('/admin/donations', [AdminController::class, 'createDonation']);
    // Route::put('/admin/donations/{id}', [AdminController::class, 'updateDonation'])->name('admin.donations.update');
    // Route::delete('/admin/donations/{id}', [AdminController::class, 'deleteDonation']); // Delete a donation
    // Route::get('/admin/donations', [AdminController::class, 'getDonations']); // Get all donations
    // Route::get('/admin/donations/{id}', [AdminController::class, 'getDonation']); // Get a single donation by ID

    // Route::post('/admin/events', [AdminController::class, 'storeEvent']); // Create an event
    // Route::put('/admin/events/{id}', [AdminController::class, 'update']); // Update an event
    // Route::get('/admin/events', [AdminController::class, 'indexEvents']); // Get all events
    // Route::delete('/admin/events/{id}', [AdminController::class, 'deleteEvent']); // Delete an event

    // Route::post('/admin/jobs', [AdminController::class, 'createJob']);
    // Route::post('/admin/events/register', [AdminController::class, 'registerForEvent']);
    // Route::post('/admin/jobs/apply', [AdminController::class, 'applyForJob']);
    // Route::post('/admin/donate', [AdminController::class, 'donate']);

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    
    Route::apiResource('donators', DonatorController::class);
    Route::apiResource('event_registrations', EventRegistrationController::class);
    Route::apiResource('job_applications', JobApplicationController::class);
    Route::apiResource('event_post', EventpostController::class);
    Route::apiResource('donation', DonationController::class);
    Route::apiResource('job_post', JobController::class);
});

// Get authenticated user
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
