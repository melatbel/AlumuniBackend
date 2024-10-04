<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    public function index()
    {
        $job = [
           'title' => 'Job Notification from Alumni Community',
           'body' => 'tHIS is for testing email',
        ];

        Mail::to('')->send(new new_job_posted($job));

        dd("Email is sent successfully");
    }
}
