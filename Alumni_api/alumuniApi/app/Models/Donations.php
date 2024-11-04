<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Donations extends Model
{
    use HasFactory;

    protected $table = 'donation';

    protected $fillable =[
        'title',
        'description',
        'amount_goal',
        'amount_raised',
        'submitted_by_name',// alumni who suggested the donation
            'image'
    ];
}
