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
            'company_name' => 'required|string|max:255', 
            'description' => 'required',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'location' => 'required',
            'deadline' => 'required|date|after_or_equal:today|date_format:Y-m-d', // Corrected date format
            ], [
                'deadline.date_format' => 'The deadline must be in the format Y-m-d.', // Uppercase Y for 4-digit year
            ]);   
    
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->messages(),
            ], 422);
        }
    
        // Store the image
        $path = $request->file('image')->store('images', 'public');
        Log::info('Image stored at: ' . $path);

        // Check for existing job with the same title, description, and location
        $existingJob = Job::where('title', $request->title)
            ->where('description', $request->description)
            ->where('location', $request->location)
            ->where('company_name', $request->company_name)
            ->first();
    
        if ($existingJob) {
            return response()->json([
                'message' => 'Job record already exists.',
            ], 409); // Conflict status code
        }
    
        // Create a new job post
        $job = Job::create([
            'title' => $request->title,
            'company_name' => $request->company_name,
            'description' => $request->description,
            'location' => $request->location,
            'image' => $path, // Use the stored image path
            'deadline' => $request->deadline,
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



    public function update(Request $request, $id)
    {
        // Find the job post by ID
        $job_post = Job::find($id);
    
        // If job post not found, return an error response
        if (!$job_post) {
            return response()->json(['message' => 'Job not found.'], 404);
        }
    
        // Validation for the fields that can be updated
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'company_name' => 'sometimes|required|string|max:255', 
            'description' => 'sometimes|required',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            'location' => 'sometimes|required',
            'deadline' => 'sometimes|required|date|after_or_equal:today|date_format:Y-m-d',
        ], [
            'deadline.date_format' => 'The deadline must be in the format Y-m-d.',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->messages(),
            ], 422);
        }
    
        // Flag to track if anything was updated
        $updated = false;
    
        // Update fields if they are present in the request
        if ($request->has('title') && $request->title !== $job_post->title) {
            $job_post->title = $request->title;
            $updated = true;
        }
    
        if ($request->has('company_name') && $request->company_name !== $job_post->company_name) {
            $job_post->company_name = $request->company_name;
            $updated = true;
        }
    
        if ($request->has('description') && $request->description !== $job_post->description) {
            $job_post->description = $request->description;
            $updated = true;
        }
    
        if ($request->has('location') && $request->location !== $job_post->location) {
            $job_post->location = $request->location;
            $updated = true;
        }
    
        // Update the image if provided
        if ($request->hasFile('image')) {
            // Store the new image
            $path = $request->file('image')->store('images', 'public');
            $job_post->image = $path; // Update the image path in the job post
            $updated = true;
        }
    
        if ($request->has('deadline') && $request->deadline !== $job_post->deadline) {
            $job_post->deadline = $request->deadline;
            $updated = true;
        }
    
        // If no fields were updated, return an error response
        if (!$updated) {
            return response()->json(['message' => 'No fields were updated.'], 400);
        }
    
        // Save the updated job post
        $job_post->save();
    
        return response()->json([
            'message' => 'Job updated successfully.',
            'data' => new JobResource($job_post),
        ], 200);
    }
    


    
public function destroy(Request $request, $id)
{
    // Find the donation by ID
    $job_post = Job::find($id);

    // If donation not found, return an error response
    if (!$job_post) {
        return response()->json([
            'message' => 'Job not found',
        ], 404);
    }

    // Optionally check if title matches if provided
    if ($request->has('title') && $request->title !== $job_post->title) {
        return response()->json([
            'message' => 'Title does not match for the specified Job',
        ], 400);
    }

    // Proceed to delete the donation
    $job_post->delete();

    return response()->json([
        'message' => 'Job Record Deleted Successfully',
    ]);
}


}
