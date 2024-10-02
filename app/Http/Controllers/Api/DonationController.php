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
        'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
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




    public function update(Request $request, Donations $donation)
    {

        \Illuminate\Support\Facades\Log::info('Request Data:', $request->all());
\Illuminate\Support\Facades\Log::info('Current Donation Data:', $donation->toArray());


        $validator = Validator::make($request->all(), [
        'title' => 'sometimes|required|string|max:255', // 'sometimes' makes it optional
        'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
        'description' => 'sometimes|required', // 'sometimes' makes it optional
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
        $imagePath = $donation->image;
    }

    // Prepare update data: only update title and description if they are present
    $updateData = [];

    if ($request->has('title') && $request->title !== $donation->title) {
        $updateData['title'] = $request->title;
    }

    if ($request->has('description') && $request->description !== $donation->description) {
        $updateData['description'] = $request->description;
    }

    if ($imagePath !== $donation->image) {
        $updateData['image'] = $imagePath;
    }

    // Log prepared update data
    Log::info('Prepared Update Data:', $updateData);

    // Check if there are any actual changes to update
    if (!empty($updateData)) {
        $donation->update($updateData);

        return response()->json([
            'message' => 'Donation Updated Successfully',
            'data' => new DonationResource($donation),
        ]);
    } else {
        return response()->json([
            'message' => 'No changes detected',
            'data' => new DonationResource($donation),
        ]);
     }
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
