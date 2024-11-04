<?php

namespace App\Http\Controllers\Api;

use App\Models\Eventpost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;

class EventpostController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $event_posts = Eventpost::all();
        return response()->json([
            'data' => EventResource::collection($event_posts),
            'message' => $event_posts->isEmpty() ? 'No record available' : null,
        ], 200);
    }

    

    public function show(Request $request, $identifier)
    {
        $event_post = Eventpost::where('id', $identifier)
            ->orWhere('event_title', $identifier)
            ->orWhere('location', $identifier)
            ->first();

        if (!$event_post) {
            return response()->json(['message' => 'Record not found.'], 404);
        }

        return new EventResource($event_post);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Eventpost::class);
    
        $validator = Validator::make($request->all(), [
            'event_title' => 'required|string|max:255',
            'description' => 'required',
            'dateTime' => 'required|date',
            'location' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpg,jpeg,png,bmp,gif,svg|max:2048',
        ]);
    
        if ($validator->fails()) {
            Log::error('Validation failed', $validator->messages());
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->messages()], 422);
        }
    
        $existingEventPost = Eventpost::where('event_title', $request->event_title)
            ->where('description', $request->description)
            ->where('dateTime', $request->dateTime)
            ->where('location', $request->location)
            ->first();
    
        if ($existingEventPost) {
            return response()->json(['message' => 'This post already exists.'], 409);
        }
    
        // Store the image
        $path = $request->file('image')->store('images', 'public');
    
        // Ensure posted_by is set
        $event_post = Eventpost::create([
            'event_title' => $request->event_title,
            'description' => $request->description,
            'dateTime' => $request->dateTime,
            'location' => $request->location,
            'image' => $path,
            'posted_by' => auth()->id(), // Ensure this is included
        ]);
    
        return response()->json(['message' => 'Event Created Successfully', 'data' => new EventResource($event_post)], 201);
    }
    
    public function update(Request $request, Eventpost $event_post)
    {
        Log::info("Updating Event Post - Current User ID: " . auth()->id() . ", Event Post Owner ID: " . $event_post->posted_by);
        $this->authorize('update', $event_post);
    
        $validator = Validator::make($request->all(), [
            'event_title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required',
            'dateTime' => 'sometimes|required|date',
            'location' => 'sometimes|required|string|max:255',
            'image' => 'sometimes|required|image|mimes:jpg,jpeg,png,bmp,gif,svg|max:2048',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'error' => $validator->messages(),
            ], 422);
        }
    
        // Handle image replacement if a new one is uploaded
        if ($request->hasFile('image')) {
            // Delete the old image
            if ($event_post->image) {
                Storage::disk('public')->delete($event_post->image);
            }
    
            // Store the new image
            $path = $request->file('image')->store('images', 'public');
            $event_post->image = $path;
        }
    
        // Update the event post details if they are provided
        $event_post->update($request->only(['event_title', 'description', 'dateTime', 'location']));
    
        return response()->json([
            'message' => 'Event Updated Successfully',
            'data' => new EventResource($event_post),
        ], 200);
    }
    
    public function destroy($id)
    {
        $event_post = Eventpost::find($id);
    
        // Check if the event exists
        if (!$event_post) {
            return response()->json(['message' => 'Event not found'], 404);
        }
        
        // Debugging lines
        $currentUserId = auth()->id();
        $eventPostOwnerId = $event_post->posted_by;
    
        // Output IDs for debugging
        \Log::info("Current User ID: $currentUserId, Event Post Owner ID: $eventPostOwnerId");
    
        // Authorize the action
        $this->authorize('delete', $event_post);
    
        // Proceed to delete the event post
        if ($event_post->image) {
            Storage::disk('public')->delete($event_post->image);
        }
        $event_post->delete();
    
        return response()->json(['message' => 'Event Deleted Successfully'], 200);
    }
    
}
