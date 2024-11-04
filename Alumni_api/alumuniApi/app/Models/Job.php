<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory;

    protected $table = 'job_post';

    protected $fillable =[
        'title',
        'description',
        'location' ,
        'image' ,
        'posted_by'
    ];

    public function applications() // Add this if you want to relate jobs and applications
    {
        return $this->hasMany(JobApplication::class);
    }
}
