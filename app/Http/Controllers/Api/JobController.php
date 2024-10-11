<?php

namespace App\Http\Controllers\Api;

use App\Models\Job;
use App\Models\User;
use App\Models\Image;
use App\Mail\NewJobPosted;
use Illuminate\Http\Request;
use App\Mail\JobNotification;
use App\Models\JobApplication;
use App\Http\Resources\JobResource;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // Correct namespace

class JobController extends Controller
{
 
    public function index()
    {
        $job_post = Job::get();

        if($job_post->count()>0)
        {
            return JobResource::collection($job_post);
        }
        else
        {
         return response()->json(['message'=> 'No record available'], 200);
        }
    }

    
    public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'title' => 'required|string|max:255',
        'description' => 'required',
        'location' => 'required',
        'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'Validation failed',
            'errors' => $validator->messages(),
        ], 422);
    }

    // Store the image
    $path = $request->file('image')->store('images', 'public');

    // Check for existing job with the same title, description, and location
    $existingJob = Job::where('title', $request->title)
        ->where('description', $request->description)
        ->where('location', $request->location)
        ->first();

    if ($existingJob) {
        return response()->json([
            'message' => 'Job record already exists.',
        ], 409); // Conflict status code
    }

    // Create a new job post, including the posted_by field
    $job = Job::create([
        'title' => $request->title,
        'description' => $request->description,
        'location' => $request->location,
        'image' => $path, // Use the stored image path
        'posted_by' => auth()->id(), // Get the currently authenticated user's ID
    ]);

    // Notify applicants by job title
    $applicants = JobApplication::where('job_id', $job->id)->get();
    foreach ($applicants as $applicant) {
        try {
            Mail::to($applicant->email)->send(new JobNotification($job->title));
        } catch (\Exception $e) {
            Log::error('Email could not be sent to: ' . $applicant->email . '. Error: ' . $e->getMessage());
        }
    }

    return response()->json([
        'message' => 'Job created successfully',
        'data' => new JobResource($job),
    ], 201);
}




    public function show(Request $request, $identifier)
    {
        $job_post = Job::where('id', $identifier)
            ->orWhere('title', $identifier)
            ->orWhere('location', $identifier)
            ->first();
        if (!$job_post) {
            return response()->json(['message' => 'Record not found.'], 404);
    }
        return new JobResource($job_post);
    }


    use AuthorizesRequests;
    public function update(Request $request, Job $job_post)
    {
        $this->authorize('update', $job_post);
        \Illuminate\Support\Facades\Log::info('Request Data:', $request->all());
        \Illuminate\Support\Facades\Log::info('Current job Data:', $job_post->toArray());

     

        $validator = Validator::make($request->all(), [
        'title' => 'sometimes|required|string|max:255', // 'sometimes' makes it optional
        'description' => 'sometimes|required',
        'location' => 'sometimes|required', 
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'Validation failed',
            'error' => $validator->messages(),
        ], 422);
    }

    // Handle image upload if present
    if ($request->hasFile('image')) {
        $image = $request->file('image');
        $imageName = time() . '_' . $image->getClientOriginalName();
        $imagePath = $image->storeAs('images', $imageName, 'public');
    } else {
        // If no new image is uploaded, keep the current image path
        $imagePath = $job_post->image;
    }

    // Prepare update data: only update title and description if they are present
    $updateData = [];

    if ($request->has('title') && $request->title !== $job_post->title) {
        $updateData['title'] = $request->title;
    }

    if ($request->has('description') && $request->description !== $job_post->description) {
        $updateData['description'] = $request->description;
    }

    if ($request->has('location') && $request->location !== $job_post->location) {
        $updateData['location'] = $request->location;
    }

    if ($imagePath !== $job_post->image) {
        $updateData['image'] = $imagePath;
    }

    // Log prepared update data
    Log::info('Prepared Update Data:', $updateData);

    // Check if there are any actual changes to update
    if (!empty($updateData)) {
        $job_post->update($updateData);

        return response()->json([
            'message' => 'Job Updated Successfully',
            'data' => new JobResource($job_post),
        ]);
    } else {
        return response()->json([
            'message' => 'No changes detected',
            'data' => new JobResource($job_post),
        ]);
     }
}


    
public function destroy(Request $request, $id)
{
    // Find the job post by ID
    $job_post = Job::find($id);

    // If job post not found, return an error response
    if (!$job_post) {
        return response()->json([
            'message' => 'Job not found',
        ], 404);
    }

    // Authorize the user to delete the job post
    $this->authorize('delete', $job_post);

    // Optionally check if title matches if provided
    if ($request->has('title') && $request->title !== $job_post->title) {
        return response()->json([
            'message' => 'Title does not match for the specified Job',
        ], 400);
    }

    // Proceed to delete the job post
    $job_post->delete();

    return response()->json([
        'message' => 'Job Record Deleted Successfully',
    ]);
}


}
