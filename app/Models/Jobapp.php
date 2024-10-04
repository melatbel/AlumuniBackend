<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jobapp extends Model
{
    use HasFactory;
    protected $table = 'job_application';

    protected $fillable =[
        'full_name',
        'phone_number',
        'email',
        'description',
        'cv'
            
    ];
}
