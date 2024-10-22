<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ChapaService;
use App\Models\Donations;
use App\Models\Donator;

class ChapaController extends Controller
{
    //
    protected $chapaService;
    public function __construct(ChapaService $chapaService)
    {
        $this->chapaService = $chapaService;
    }

    public function initializePayment(Request $request)
    {
        $data = [
            'amount' => $request->input('amount'),
            'currency' => 'ETB',
            'email' => $request->input('email'),
            'phone_number' => $request->input('phone_number'),
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'tx_ref' => uniqid('tx_', true),
            'callback_url' => url('/payment/callback'),
        ];

        $response = $this->chapaService->initializePayment($data);
        //return response()->json($response);
        if ($response['status'] === 'success') { // Assuming 'status' indicates the payment success

            // Payment details returned by Chapa
            $paymentData = $data;

            // // Assuming that the `donation_id` is passed in the initialization or callback.
            $donationId = '1'; // Or any other way you're passing donation_id.

            // // Find the related donation campaign
            $donation = Donations::findOrFail($donationId);

            // // Save donator's data after payment verification
            Donator::create([
                'donation_id'   => $donation->id, // Store the donation campaign id
                'campaign_name' => $donation->title, // Assuming your Donations model has 'title'
                'full_name'     => $paymentData['first_name'] . ' ' . $paymentData['last_name'],
                'phone_number'  => $paymentData['phone_number'] ?? null, // Add phone if available
                'email'         => $paymentData['email'],
                'amount'        => $paymentData['amount'],
            ]);

            return response()->json([
                'message' => 'Payment verified and Donator data stored successfully.',
                'data' => $response['data']['checkout_url'],
            ], 200);
        }

        // Handle failed payment
        return response()->json([
            'message' => 'Payment verification failed.',
        ], 400);
        return response()->json($response);

    }

    public function verifyPayment($transactionId){

        $response = $this->chapaService->verifyPayment($transactionId);
        return response()->json($response);
    }


}
