<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'renter_id',
        'apartment_id',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    protected $appends = ['booking_state'];

    public function getBookingStateAttribute()
    {
        $today = Carbon::today();

        if ($this->end_date < $today) {
            return 'ended';
        }

        if ($this->start_date > $today) {
            return 'upcoming';
        }

        return 'ongoing';
    }
}

