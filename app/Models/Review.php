<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'booking_id',
        'apartment_id',
        'renter_id',
        'rating',
        'review',
    ];

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function renter()
    {
        return $this->belongsTo(User::class, 'renter_id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
