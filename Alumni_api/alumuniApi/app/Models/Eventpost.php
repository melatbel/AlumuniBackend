<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Eventpost extends Model
{
    use HasFactory;

    protected $table = 'event_post';

    protected $fillable =[
        'event_title',
        'description',
        'dateTime',
        'location',
        'image',
        'posted_by'
            
    ];
}
