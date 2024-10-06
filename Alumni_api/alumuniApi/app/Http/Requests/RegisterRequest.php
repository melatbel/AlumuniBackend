public function rules()
{
    $allowedDepartments = ['Computer Science', 'Information Technology'];

    return [
        'first_name' => 'required|string|max:255',
        'middle_name' => 'nullable|string|max:255',
        'last_name' => 'required|string|max:255',
        'gender' => 'required|in:male,female',
        'email' => 'required|email|unique:users,email|max:255',
        'password' => 'required|string|min:8',
        'role' => 'required|in:admin,alumni,student',
        'department' => 'required|string|in:' . implode(',', $allowedDepartments),
        'batch' => 'required|string|max:255',
        'phone_number' => 'required|string|max:15',
        'linkedin_profile' => 'nullable|url|max:255',
        'graduation_certificate' => 'required_if:role,alumni|file|mimes:pdf|max:2048',
        'university_id' => [
            'required_if:role,student',
            'string',
            'max:255',
            'regex:/^\d{3}\/BUN-B\d\/\d{4}$/',
        ],
    ];
}
