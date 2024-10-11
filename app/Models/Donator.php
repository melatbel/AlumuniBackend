<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Donator extends Model
{
    use HasFactory;

    protected $fillable = [
        'donation_id',
        'campaign_name',
        'full_name',
        'phone_number',
        'email',
        'amount',
        'campaign_name',
    
    ];

    // Define relationship with Donations
    public function donation()
    {
        return $this->belongsTo(Donations::class);
    }
}
