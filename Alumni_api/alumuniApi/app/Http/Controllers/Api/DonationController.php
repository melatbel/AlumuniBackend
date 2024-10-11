<?php

namespace App\Http\Controllers\Api;
use App\Models\Image;
use App\Models\Donations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\DonationResource;
use Illuminate\Support\Facades\Validator;



class DonationController extends Controller
{
    public function index()
    {
        $donation = Donations::get();

        if($donation->count()>0)
        {
            return DonationResource::collection($donation);
        }
        else
        {
         return response()->json(['message'=> 'No record available'], 200);
        }
    }


    public function store(Request $request)
{
    $validator = Validator::make($request->all(),[
        'title' => 'required|string|max:255',
        'image' => 'required|image|mimes:jpg,jpeg,png,bmp,gif,svg|max:2048',
        'description' => 'required',
    ]);

    if($validator->fails()) {
        return response()->json([
            'message'=> 'All fields are mandatory',
            'error' => $validator->messages(), 
        ], 422);
    }

    // Store the image
    $path = $request->file('image')->store('images', 'public'); 

    // Check if a donation with the same title, description, and image already exists
    $existingDonation = Donations::where('title', $request->title)
        ->where('description', $request->description)
        ->first();

    if ($existingDonation) {
        return response()->json([
            'message' => 'Donation Record already exists.',
        ], 409); // Conflict status code
    }

    // Create and store the image data
    $image = Image::create([
        'filename' => $path,
    ]);

    // Store the donation data, including the image path
    $donation = Donations::create([
        'title' => $request->title,
        'image' => $image->filename,  // store the image path in the donation
        'description' => $request->description,
    ]);

    // Return both the donation and image data
    return response()->json([
        'message' => 'Donation Created Successfully',
        'data' => new DonationResource($donation)
    ], 201);
}

    

    public function show(Request $request, $identifier)
    {
        $Donations = Donations::where('id', $identifier)
        ->orWhere('title', $identifier)
        ->first();
    if (!$Donations) {
        return response()->json(['message' => 'Record not found.'], 404);
    }
        return new DonationResource($Donations);
    }




    public function update(Request $request, $id)
    {
        // Find the donation by ID
        $donation = Donations::find($id);
    
        // If donation not found, return an error response
        if (!$donation) {
            return response()->json(['message' => 'Donation not found.'], 404);
        }
    
        // Validation for the fields that can be updated
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'image' => 'sometimes|image|mimes:jpg,jpeg,png,bmp,gif,svg|max:2048',
            'description' => 'sometimes|required',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'error' => $validator->messages(),
            ], 422);
        }
    
        // Flag to track if anything was updated
        $updated = false;
    
        // Update fields if they are present in the request
        if ($request->has('title') && $request->title !== $donation->title) {
            $donation->title = $request->title;
            $updated = true;
        }
    
        if ($request->has('description') && $request->description !== $donation->description) {
            $donation->description = $request->description;
            $updated = true;
        }
    
        // Update the image if provided
        if ($request->hasFile('image')) {
            // Store the new image
            $path = $request->file('image')->store('images', 'public');
            $donation->image = $path; // Update the image path in the donation
            $updated = true;
        }
    
        // If no fields were updated, return an error response
        if (!$updated) {
            return response()->json(['message' => 'No fields were updated.'], 400);
        }
    
        // Save the updated donation
        $donation->save();
    
        return response()->json([
            'message' => 'Donation updated successfully.',
            'data' => new DonationResource($donation),
        ], 200);
    }
    

    
public function destroy(Request $request, $id)
{
    // Find the donation by ID
    $donation = Donations::find($id);

    // If donation not found, return an error response
    if (!$donation) {
        return response()->json([
            'message' => 'Donation not found',
        ], 404);
    }

    // Optionally check if title matches if provided
    if ($request->has('title') && $request->title !== $donation->title) {
        return response()->json([
            'message' => 'Title does not match for the specified donation',
        ], 400);
    }

    // Proceed to delete the donation
    $donation->delete();

    return response()->json([
        'message' => 'Donation Record Deleted Successfully',
    ]);
}

}
