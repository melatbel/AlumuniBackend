<?php

namespace App\Http\Controllers\Api;

use App\Mail\NewJobPosted;
use Illuminate\Http\Request;
use App\Models\JobApplication;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\JobApplicationRequest;
use App\Http\Resources\JobApplicationResource;

class JobApplicationController extends Controller
{

    public function index()
    {
        $applications = JobApplication::with('job')->get();
        return response()->json($applications);
    }

    public function store(Request $request)
    {
    $validator = Validator::make($request->all(), [
        'job_id' => 'required|exists:job_post,id',
        'full_name' => 'required|string|max:255',
        'phone_number' => [
            'required',
            'regex:/^(?:\+251|0)?9\d{8}$/', // Validate Ethiopian phone numbers
        ],
        'email' => 'required|email',
        'description' => 'required|string',
        'cv' => 'required|file|mimes:pdf,doc,docx|max:2048', // Ensure single file is uploaded
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'Validation failed',
            'errors' => $validator->messages(),
        ], 422);
    }

    // Fetch the job details to check the deadline
    $job = JobApplication::find($request->job_id);
    if ($job->deadline < now()) { // Check if the deadline has passed
        return response()->json([
            'message' => 'The application deadline has passed.',
        ], 403); // Forbidden status code
    }

    // Check if the user has already applied for this job
    $existingApplication = JobApplication::where('job_id', $request->job_id)
        ->where('email', $request->email) // You can also check with phone number
        ->first();

    if ($existingApplication) {
        return response()->json([
            'message' => 'You have already applied for this job.',
        ], 409); // Conflict status code
    }

    // Ensure only one file is uploaded for the CV
    if (is_array($request->file('cv'))) {
        return response()->json([
            'message' => 'Only one CV file can be uploaded.',
        ], 422);
    }

    // Store the CV file
    $cvPath = $request->file('cv')->store('cvs', 'public');

    // Create the application
    $application = JobApplication::create([
        'job_id' => $request->job_id,
        'full_name' => $request->full_name,
        'phone_number' => $request->phone_number,
        'email' => $request->email,
        'description' => $request->description,
        'cv' => $cvPath,
    ]);

    return response()->json([
        'message' => 'Job application submitted successfully!',
        'data' => $application,
    ], 201);
}



    public function show($id)
    {
        $application = JobApplication::with('job')->find($id);

        if (!$application) {
            return response()->json(['message' => 'Application not found'], 404);
        }

        return response()->json($application);
    }}
