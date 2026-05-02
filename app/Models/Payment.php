<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{

    protected $fillable = [
        'booking_id',
        'amount',
        'commission',
        'artisan_amount',
        'status',
        'stripe_session_id'
    ];
    
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
