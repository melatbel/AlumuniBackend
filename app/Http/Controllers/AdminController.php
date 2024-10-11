<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Donations; // Ensure correct model name
use App\Models\Donator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\EventRegistration;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\EventResource;
use App\Models\EventPost;
class AdminController extends Controller
{
    // Create a new donation post
    public function createDonation(Request $request)
    {
        \Log::info('Donation creation attempt', $request->all());
    
        // Check if the user is authenticated
        if (!auth()->check()) {
            \Log::warning('Unauthorized access attempt.');
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    
        // Validate the request
        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'amount_goal' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Validate the image
        ]);
    
        // Remove commas from amount_goal
        $request->merge([
            'amount_goal' => str_replace(',', '', $request->input('amount_goal')),
        ]);
    
        // Handle the image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imagePath = $image->store('donations_images', 'public'); // Store in 'public/storage/donations_images'
        }
    
        // Create the donation
        $donation = Donations::create([
            'title' => $request->title,
            'description' => $request->description,
            'amount_goal' => $request->input('amount_goal'),
            'submitted_by_name' => $request->submitted_by_name, // Store the alumni's name
            'amount_raised' => 0, // Initialize amount_raised to 0
            'image' => $imagePath, // Save the image path if available
        ]);
    
        return response()->json(['donation' => $donation], 201);
    }
    

    public function updateDonation(Request $request, $id)
    {
        // Find the donation by ID
        $donation = Donations::find($id);
    
        // Check if the donation exists
        if (!$donation) {
            return response()->json(['message' => 'Donation not found'], 404);
        }
    
        // Validate the incoming request
        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'amount_goal' => 'required|numeric',
            'submitted_by_name' => 'required|string', // Assuming you want to include this
            'image' => 'nullable|image|mimes:jpg,jpeg,png,bmp,gif,svg|max:2048', // Validate the new image
        ]);
    
        // Check if a new image was uploaded
        if ($request->hasFile('image')) {
            // Delete the old image from storage if it exists
            if ($donation->image) {
                Storage::disk('public')->delete($donation->image);
            }
    
            // Store the new image
            $path = $request->file('image')->store('donations_images', 'public');
    
            // Update the image field in the donation
            $donation->image = $path;
        }
    
        // Update the other fields
        $donation->update([
            'title' => $request->title,
            'description' => $request->description,
            'amount_goal' => $request->input('amount_goal'),
            'submitted_by_name' => $request->submitted_by_name, // Update the submitted_by_name field
        ]);
    
        return response()->json(['message' => 'Donation updated successfully', 'donation' => $donation], 200);
    }
    


public function deleteDonation($id)
{
    // Find the donation
    $donation = Donations::find($id);

    if (!$donation) {
        return response()->json(['message' => 'Donation not found'], 404);
    }

    // Delete the donation
    $donation->delete();

    return response()->json(['message' => 'Donation deleted successfully'], 200);
}
 

public function getDonations()
{
    try {
        $donations = Donations::all(); // Assuming you have a Donation model
        return response()->json($donations);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to retrieve donations', 'message' => $e->getMessage()], 500);
    }
}

public function getDonation($id)
{
    $donation = Donations::find($id);
    if (!$donation) {
        return response()->json(['message' => 'Donation not found'], 404);
    }
    return response()->json($donation, 200);
}





public function storeEvent(Request $request)
{
    $request->validate([
        'event_title' => 'required|string|max:255',
        'description' => 'required|string',
        'dateTime' => 'required|date',
        'location' => 'required|string|max:255',
        'image' => 'nullable|image|mimes:jpg,jpeg,png,bmp,gif,svg|max:2048',
    ]);

    // Handle image upload if exists
    $imagePath = $request->file('image') ? $request->file('image')->store('images', 'public') : null;

    $event = EventPost::create([
        'event_title' => $request->event_title,
        'description' => $request->description,
        'dateTime' => $request->dateTime,
        'location' => $request->location,
        'image' => $imagePath,
    ]);

    return response()->json($event, 201);
}

public function update(Request $request, $id)
{
    // Log incoming request data
    Log::info('Incoming Request Data:', $request->all());

    // Find the event post by ID
    $event_post = EventPost::findOrFail($id);

    // Log current event data before update
    Log::info('Current Event Data:', $event_post->toArray());

    // Validation rules
    $validator = Validator::make($request->all(), [
        'event_title' => 'sometimes|required|string|max:255',
        'description' => 'sometimes|required|string',
        'dateTime' => 'sometimes|required|date',
        'location' => 'sometimes|required|string',
        'image' => 'sometimes|image|mimes:jpg,jpeg,png,bmp,gif,svg|max:2048'
    ]);

    // Check for validation errors
    if ($validator->fails()) {
        return response()->json([
            'message' => 'Validation failed',
            'error' => $validator->messages(),
        ], 422);
    }

    // Prepare an array to hold update data
    $updateData = [];

    // Check each field and compare with current values
    if ($request->has('event_title')) {
        if ($request->event_title !== $event_post->event_title) {
            $updateData['event_title'] = $request->event_title;
        } else {
            Log::info('event_title unchanged');
        }
    }
    if ($request->has('description')) {
        if ($request->description !== $event_post->description) {
            $updateData['description'] = $request->description;
        } else {
            Log::info('description unchanged');
        }
    }
    if ($request->has('dateTime')) {
        if ($request->dateTime !== $event_post->dateTime) {
            $updateData['dateTime'] = $request->dateTime;
        } else {
            Log::info('dateTime unchanged');
        }
    }
    if ($request->has('location')) {
        if ($request->location !== $event_post->location) {
            $updateData['location'] = $request->location;
        } else {
            Log::info('location unchanged');
        }
    }

    // Handle image upload if present
    if ($request->hasFile('image')) {
        // Delete the old image from storage if it exists
        if ($event_post->image) {
            Storage::disk('public')->delete($event_post->image);
        }
        // Store the new image
        $path = $request->file('image')->store('images', 'public');
        $updateData['image'] = $path; // Add the new image path to the update array
    }

    // Log the prepared update data
    Log::info('Prepared Update Data:', $updateData);

    // Check if there are any actual changes to update
    if (!empty($updateData)) {
        // Update the event post with new data
        $event_post->update($updateData);

        return response()->json([
            'message' => 'Event Updated Successfully',
            'data' => new EventResource($event_post), // Return the updated event
        ], 200);
    } else {
        return response()->json([
            'message' => 'No changes detected',
            'data' => new EventResource($event_post),
        ], 200);
    }
}


// Get all events
public function indexEvents()
{
    $events = EventPost::all();
    return response()->json($events, 200);
}

// Delete an event
public function deleteEvent($id)
{
    $event = EventPost::findOrFail($id);
    // Delete the image if it exists
    Storage::disk('public')->delete($event->image);
    $event->delete();

    return response()->json(['message' => 'Event deleted successfully'], 200);
}




    // Create a new job post
    public function createJob(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'location' => 'required|string',
            'image' => 'required|string', // Assuming the image is a URL or path
        ]);

        // Create the job post
        $job = Job::create($request->all());
        return response()->json($job, 201);
    }

    // Register for an event
    public function registerForEvent(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'event_post_id' => 'required|integer|exists:event_posts,id',
            'full_name' => 'required|string',
            'phone_number' => 'required|string',
            'email' => 'required|email',
        ]);

        // Create the event registration
        $registration = EventRegistration::create([
            'event_post_id' => $request->event_post_id,
            'full_name' => $request->full_name,
            'phone_number' => $request->phone_number,
            'email' => $request->email,
        ]);

        return response()->json($registration, 201);
    }

    // Apply for a job
    public function applyForJob(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'job_id' => 'required|integer|exists:job_posts,id',
            'full_name' => 'required|string',
            'phone_number' => 'required|string',
            'email' => 'required|email',
            'description' => 'required|string',
            'cv' => 'required|string', // Assuming this is a path to the CV file
        ]);

        // Create the job application
        $application = JobApplication::create([
            'job_id' => $request->job_id,
            'full_name' => $request->full_name,
            'phone_number' => $request->phone_number,
            'email' => $request->email,
            'description' => $request->description,
            'cv' => $request->cv,
        ]);

        return response()->json($application, 201);
    }
}
