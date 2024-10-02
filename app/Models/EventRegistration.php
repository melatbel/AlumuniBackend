<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EventRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_post_id',
        'full_name',
        'phone_number',
        'email',
    ];

    public function event()
    {
        return $this->belongsTo(Eventpost::class);
    }
}
