<?php

namespace App\Http\Controllers\Api;


use App\Models\Image;
use App\Models\Eventpost;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class EventpostController extends Controller
{

    public function index()
    {
        $event_post = Eventpost::get();

        if($event_post->count()>0)
        {
            return EventResource::collection($event_post);
        }
        else
        {
         return response()->json(['message'=> 'No record available'], 200);
        }
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_title' => 'required|string|max:255',
            'description' => 'required',
            'dateTime' => 'required',
            'location' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpg,jpeg,png,bmp,gif,svg|max:2048',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'message' => 'All fields are mandatory',
                'error' => $validator->messages(),
            ], 422);
        }
    
        // Store the image
        $path = $request->file('image')->store('images', 'public');
    
        // Check for existing event with the same details
        $existingEventPost = Eventpost::where('event_title', $request->event_title)
            ->where('description', $request->description)
            ->where('dateTime', $request->dateTime)
            ->where('location', $request->location)
            ->first();
    
        if ($existingEventPost) {
            return response()->json([
                'message' => 'This post already exists.',
            ], 409); // Conflict status code
        }
    
        // Create the new event post
        $event_post = Eventpost::create([
            'event_title' => $request->event_title,
            'description' => $request->description,
            'dateTime' => $request->dateTime,
            'location' => $request->location,
            'image' => $path,
        ]);
    
        return response()->json([
            'message' => 'Event Created Successfully',
            'data' => new EventResource($event_post),
        ], 201);
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



    public function update(Request $request, Eventpost $id)
    {

        \Illuminate\Support\Facades\Log::info('Request Data:', $request->all());
        \Illuminate\Support\Facades\Log::info('Current job Data:', $id->toArray());


        $validator = Validator::make($request->all(), [
        'event_title' => 'sometimes|required|string|max:255', // 'sometimes' makes it optional
        'description' => 'sometimes|required',
        'dateTime' => 'sometimes|required',
        'location' => 'sometimes|required', 
        'image' => 'sometimes|required|image|mimes:jpg,jpeg,png,bmp,gif,svg|max:2048'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'Validation failed',
            'error' => $validator->messages(),
        ], 422);
    }

    $image = Image::findOrFail($id);
        
        // Delete the old image from storage
        Storage::disk('public')->delete($image->filename);

        // Store the new image
        $path = $request->file('image')->store('images', 'public');

        // Update the image record in the database
        $image->update([
            'filename' => $path,
        ]);

        return response()->json($image, 200);
        
    // Check if there are any actual changes to update
    if (!empty($updateData)) {
        $event_post->update($updateData);

        return response()->json([
            'message' => 'Event Updated Successfully',
            'data' => new EventResource($event_post),
        ]);
    } else {
        return response()->json([
            'message' => 'No changes detected',
            'data' => new EventResource($event_post),
        ]);
     }
}


    
public function destroy(Request $request, $id)
{
    // Find the donation by ID
    $event_post = Eventpost::find($id);

    // If donation not found, return an error response
    if (!$event_post) {
        return response()->json([
            'message' => 'Job not found',
        ], 404);
    }

    // Optionally check if title matches if provided
    if ($request->has('event_title') && $request->title !== $event_post->title) {
        return response()->json([
            'message' => 'Title does not match for the specified Job',
        ], 400);
    }

    // Proceed to delete the donation
    $event_post->delete();

    return response()->json([
        'message' => 'Event Deleted Successfully',
    ]);
}

}
