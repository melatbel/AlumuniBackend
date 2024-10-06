<?php

namespace App\Http\Controllers\Api;

use App\Models\Eventpost;
use Illuminate\Http\Request;
use App\Models\EventRegistration;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\EventRegistrationResource;

class EventRegistrationController extends Controller
{
    public function index()
    {
        $event_posts = Eventpost::all(); // Get all event posts

        if ($event_posts->isEmpty()) {
            return response()->json(['message' => 'No records available'], 200);
        }

        return response()->json($event_posts);
    }

    public function store(Request $request)
  {
    // Validate the request
    $validator = Validator::make($request->all(), [
        'event_post_id' => 'required|exists:event_post,id', // Make sure to use the correct table name
        'full_name' => 'required|string|max:255',
        'phone_number' => [
            'required',
            'regex:/^(?:\+251|0)?9\d{8}$/', // Validate Ethiopian phone numbers
        ],
        'email' => 'required|email|max:255',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'Validation error',
            'errors' => $validator->errors(),
        ], 422);
    }

    // Get the event ID from the request
    $event_post_id = $request->event_post_id;

    // Check if the event exists
    $event = Eventpost::find($event_post_id);
    if (!$event) {
        return response()->json(['message' => 'Event not found'], 404);
    }

    // Check if the user has already registered for this event
    $existingRegistration = EventRegistration::where('event_post_id', $event_post_id)
        ->where('phone_number', $request->phone_number)
        ->orWhere('email', $request->email)
        ->first();

    if ($existingRegistration) {
        return response()->json(['message' => 'You have already registered for this event'], 409);
    }

    // Create the registration
    $registration = EventRegistration::create([
        'event_post_id' => $event_post_id,
        'full_name' => $request->full_name,
        'phone_number' => $request->phone_number,
        'email' => $request->email,
    ]);

    return response()->json([
        'message' => 'Registration successful',
        'data' => $registration,
    ], 201);
}
}
