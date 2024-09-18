<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job_Post extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'description',
        'posted_by',
        'location',
        'deadline',
        'date_posted',
        'contact_email'
    ];
}
