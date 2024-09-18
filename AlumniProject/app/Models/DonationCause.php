<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DonationCause extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'description',
        'amount_goal',
        'amount_raised',
        'submitted_by',
    ];
}
