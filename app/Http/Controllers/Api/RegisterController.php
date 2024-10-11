<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\API\BaseController as BaseController;

class RegisterController extends BaseController
{
    public function register(Request $request): JsonResponse
{
    $allowedDepartments = ['Computer Science', 'Business Management', 'Accounting', 'Digital Marketing']; 
    
    // Validate the incoming request data
    $validator = Validator::make($request->all(), [
        'first_name' => 'required|alpha',
        'middle_name' => 'required|alpha',
        'last_name' => 'required|alpha',
        'gender' => 'required|in:male,female',
        'password' => 'required|min:8',
        'phone_number' => [
            'required',
            'regex:/^(\+251-\d{9}|\d{10})$/'
        ],
        'email' => 'required|email|unique:users',
        'user_type' => 'required|in:student,alumni',
        'linkedin_profile' => 'required|url',
        'department' => 'required|in:' . implode(',', $allowedDepartments),
        'graduation_certificate_path' => 'required_if:user_type,student|file|mimes:pdf,jpg,jpeg,png|max:2048', // Add this line
    ]);

    // Additional validation for students or alumni
    if ($request->user_type === 'student') {
        // Require 'id_path' and 'batch' for students
        $validator->after(function ($validator) use ($request) {
            if (empty($request->id_path)) {
                $validator->errors()->add('id_path', 'The ID path field is required for students.');
            }
            if (empty($request->batch)) {
                $validator->errors()->add('batch', 'The batch field is required for students.');
            }
        });
    } elseif ($request->user_type === 'alumni') {
        // Alumni should not upload ID path and batch
        $validator->after(function ($validator) use ($request) {
            if (!empty($request->id_path)) {
                $validator->errors()->add('id_path', 'Alumni should not upload ID path.');
            }
            if (!empty($request->batch)) {
                $validator->errors()->add('batch', 'Alumni should not fill batch.');
            }
        });
    }

    // Check if validation fails
    if ($validator->fails()) {
        return $this->sendError('Validation Error.', $validator->errors());       
    }

    // Handle file upload for graduation certificate
    $graduationCertificatePath = null;
    if ($request->hasFile('graduation_certificate_path')) {
        $graduationCertificatePath = $request->file('graduation_certificate_path')->store('certificates', 'public'); // Adjust the storage location as needed
    }

    // Create the user after validation
    $user = User::create([
        'first_name' => $request->first_name,
        'middle_name' => $request->middle_name,
        'last_name' => $request->last_name,
        'gender' => $request->gender,
        'password' => Hash::make($request->password), // Hash the password
        'department' => $request->department,
        'batch' => $request->user_type === 'student' ? $request->batch : null, // Set to null for alumni
        'phone_number' => $request->phone_number,
        'email' => $request->email,
        'user_type' => $request->user_type,
        'id_path' => $request->user_type === 'alumni' ? null : $request->id_path, // Set to null for alumni
        'linkedin_profile' => $request->linkedin_profile,
        'graduation_certificate_path' => $graduationCertificatePath, // Save the file path here
    ]);

    // Prepare success response
    $success['token'] = $user->createToken('MyApp')->plainTextToken; 
    $success['first_name'] = $user->first_name;

    return $this->sendResponse($success, 'User registered successfully.');
}

    

    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request): JsonResponse
    {
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        // Attempt to log the user in
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            $success['token'] = $user->createToken('MyApp')->plainTextToken;
            $success['first_name'] = $user->first_name;

            return $this->sendResponse($success, 'User logged in successfully.');
        } else {
            return $this->sendError('Unauthorized.', ['error' => 'Invalid credentials.']);
        }
    }
}
