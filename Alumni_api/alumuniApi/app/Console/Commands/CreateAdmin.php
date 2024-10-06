<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class CreateAdmin extends Command
{
    protected $signature = 'make:admin {first_name} {email} {password}';
    protected $description = 'Create a new admin user';

    public function handle()
    {
        $firstName = $this->argument('first_name');
        $email = $this->argument('email');
        $password = bcrypt($this->argument('password'));

        $admin = User::create([
            'first_name' => $firstName,
            'email' => $email,
            'password' => $password,
            'role' => 'admin', // Set the role to admin
            'is_approved' => true, // Automatically approve the admin
        ]);

        $this->info('Admin created successfully: ' . $admin->email);
    }
}
