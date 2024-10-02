<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    use HasFactory;
 
    protected $fillable = [
        'job_id',
        'full_name',
        'phone_number',
        'email',
        'description',
        'cv'
    ];

    public function job() // Add this for relationship
    {
        return $this->belongsTo(Job::class, 'job_id');
    }

}
