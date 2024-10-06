<?php

namespace App\Http\Controllers\Api;

use App\Models\Donator;
use App\Models\Donations;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\DonatorResource;
use Illuminate\Support\Facades\Validator;

class DonatorController extends Controller
{
    public function index()
    {
        $donators = Donator::all();
        return DonatorResource::collection($donators);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'donation_id' => 'required|exists:donation,id', // Ensure the donation exists
            'full_name' => 'required|string|max:255',
            'campaign_name' => 'required|string|max:255',
            'phone_number' => [
                        'required',
                        'regex:/^(?:\+251|0)?9\d{8}$/', // Validate Ethiopian phone numbers
                    ],
            'email' => 'required|email|unique:donators',
            'amount' => 'required|numeric|min:1',

        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error.', 'errors' => $validator->errors()], 422);
        }

        // Check if there is an active donation post
        $donation = Donations::find($request->donation_id);
        if (!$donation) {
            return response()->json(['message' => 'Donation post not found.'], 404);
        }

        // Create the donator entry
        $donator = Donator::create([
            'full_name' => $request->full_name,
            'phone_number' => $request->phone_number,
            'email' => $request->email,
            'campaign_name' => $request->campaign_name,
            'amount' => $request->amount,
            'donation_id' => $request->donation_id,
        ]);

        return response()->json(['message' => 'Donation successful.', 'data' => new DonatorResource($donator)], 201);
    }
}
