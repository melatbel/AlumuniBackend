<?php

namespace App\Http\Controllers\Api;

use App\Models\Image;
use App\Models\Survey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\SurveyResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SurveyController extends Controller
{

    public function index()
    {
        $surveys = Survey::get();

        if($surveys->count()>0)
        {
            return SurveyResource::collection($surveys);
        }
        else
        {
         return response()->json(['message'=> 'No record available'], 200);
        }
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'survey_title' => 'required|string|max:255',
            'description' => 'required|string',
            'survey_link' => 'required|url',
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
        $existingSurvey = Survey::where('survey_title', $request->survey_title)
            ->where('description', $request->description)
            ->where('survey_link', $request->survey_link)
            ->first();
    
         // If an existing survey is found, return a conflict response
        if ($existingSurvey) {
            return response()->json([
                'message' => 'This survey already exists.',
            ], 409); // Conflict status code
        }
    
        // Create the new survey post
        $surveys = Survey::create([
            'survey_title' => $request->survey_title,
            'description' => $request->description,
            'survey_link' => $request->survey_link,
            'image' => $path,
        ]);
    
        return response()->json([
            'message' => 'Survey Created Successfully',
            'data' => new SurveyResource($surveys),
        ], 201);
    }
    
    


    public function show(Request $request, $identifier)
    {
        $surveys = Survey::where('id', $identifier)
            ->orWhere('survey_title', $identifier)
            ->first();
        if (!$surveys) {
            return response()->json(['message' => 'Record not found.'], 404);
    }
        return new SurveyResource($surveys);
    }



    public function update(Request $request, Survey $survey)
{
    Log::info('Request Data:', $request->all());
    Log::info('Current Survey Data:', $survey->toArray());

    $validator = Validator::make($request->all(), [
        'survey_title' => 'sometimes|required|string|max:255',
        'description' => 'sometimes|required|string',
        'survey_link' => 'sometimes|required|url',
        'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'Validation failed',
            'errors' => $validator->messages(),
        ], 422);
    }

    // Handle image upload if present
    if ($request->hasFile('image')) {
        // Store the new image
        $imagePath = $request->file('image')->store('images', 'public');
        $survey->image = $imagePath; // Update image path
    }

    // Prepare an array to hold fields that need updating
    $updatedFields = [];

    // Update other fields if present and if they have changed
    if ($request->has('survey_title') && $survey->survey_title !== $request->survey_title) {
        $survey->survey_title = $request->survey_title;
        $updatedFields['survey_title'] = $request->survey_title; // Track change
    }

    if ($request->has('description') && $survey->description !== $request->description) {
        $survey->description = $request->description;
        $updatedFields['description'] = $request->description; // Track change
    }

    if ($request->has('survey_link') && $survey->survey_link !== $request->survey_link) {
        $survey->survey_link = $request->survey_link;
        $updatedFields['survey_link'] = $request->survey_link; // Track change
    }

    // Only save if there are changes
    if (!empty($updatedFields)) {
        $survey->save();
        return response()->json([
            'message' => 'Survey updated successfully',
            'data' => new SurveyResource($survey),
        ]);
    }

    return response()->json([
        'message' => 'No changes detected',
        'data' => new SurveyResource($survey),
    ]);
}

 
    

    
public function destroy(Request $request, $id)
{
    // Find the donation by ID
    $surveys = Survey::find($id);

    // If donation not found, return an error response
    if (!$surveys) {
        return response()->json([
            'message' => 'Survey not found',
        ], 404);
    }

    // Optionally check if title matches if provided
    if ($request->has('survey_title') && $request->title !== $surveys->title) {
        return response()->json([
            'message' => 'Title does not match for the specified Survey',
        ], 400);
    }

    // Proceed to delete the donation
    $surveys->delete();

    return response()->json([
        'message' => 'Survey Deleted Successfully',
    ]);
}
}
