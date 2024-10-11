<?php

namespace App\Http\Controllers\Api;

use App\Models\Image;
use App\Models\User;

use App\Models\Donations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\DonationResource;
use Illuminate\Support\Facades\Validator;
use App\Models\Donator; // Ensure you import the Donator model
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;

class DonationController extends Controller
{
    
    use AuthorizesRequests;

    // Method to view all donations
    public function index()
    {
        // Authorize viewing donations for any user
        $this->authorize('viewAny', Donations::class); 
        $donations = Donations::all();
        return DonationResource::collection($donations);
    }

    // Method to store a new donation (only for admin)
    public function store(Request $request)
    {
        $user = auth()->user();
        $this->authorize('create', Donations::class); // Only admins can create

        Log::info('Checking create permissions for user type: ' . $user->user_type);

        // Validate the request
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->messages()], 422);
        }

        // Store the image
        $path = $request->file('image')->store('images', 'public');

        // Check if a donation with the same title and description already exists
        $existingDonation = Donations::where('title', $request->title)
            ->where('description', $request->description)
            ->first();

        if ($existingDonation) {
            return response()->json(['message' => 'Donation record already exists.'], 409);
        }

        // Create a new donation record
        $donation = Donations::create([
            'title' => $request->title,
            'image' => $path,
            'description' => $request->description,
            'amount_goal' => $request->input('amount_goal'), // Ensure this field is also handled
            'submitted_by_name' => $request->submitted_by_name, // Handle this as well
        ]);

        return response()->json(['message' => 'Donation created successfully', 'data' => new DonationResource($donation)], 201);
    }

    // Method to show a specific donation
    public function show($identifier)
    {
        $donation = Donations::where('id', $identifier)
            ->orWhere('title', $identifier)
            ->first();

        if (!$donation) {
            return response()->json(['message' => 'Record not found.'], 404);
        }

        return new DonationResource($donation);
    }

    // Method to update a donation (only for admin)
    public function update(Request $request, $id)
    {
        $donation = Donations::find($id);
        $this->authorize('update', $donation); // Only admins can update donations

        if (!$donation) {
            return response()->json(['message' => 'Donation not found'], 404);
        }

        Log::info('Update request for Donation ID: ' . $id, $request->all());

        // Validate the request
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'amount_goal' => 'sometimes|required|numeric',
            'submitted_by_name' => 'sometimes|required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->messages()], 422);
        }

        // Handle image upload if provided
        if ($request->hasFile('image')) {
            if ($donation->image) {
                Storage::disk('public')->delete($donation->image);
            }
            $donation->image = $request->file('image')->store('donations_images', 'public');
        }

        // Update fields if they are present in the request
        $donation->update($request->only(['title', 'description', 'amount_goal', 'submitted_by_name']));

        return response()->json(['message' => 'Donation updated successfully', 'data' => new DonationResource($donation)], 200);
    }

    // Method to delete a donation (only for admin)
    public function destroy(Request $request, $id)
    {
        $donation = Donations::find($id);
        $this->authorize('delete', $donation); // Only admins can delete donations

        if (!$donation) {
            return response()->json(['message' => 'Donation not found'], 404);
        }

        // Delete the image if it exists
        if ($donation->image) {
            Storage::disk('public')->delete($donation->image);
        }

        // Delete the donation
        $donation->delete();
        return response()->json(['message' => 'Donation record deleted successfully']);
    }
}
