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

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d');
    }


    public function getBookingStateAttribute()
    {
        if (empty($this->start_date) || empty($this->end_date)) {
            return null;
        }

        $today = Carbon::today();

        $start = Carbon::parse($this->start_date)->startOfDay();
        $end   = Carbon::parse($this->end_date)->endOfDay();

        if ($end->lt($today)) {
            return 'ended';
        }

        if ($start->gt($today)) {
            return 'upcoming';
        }

        return 'ongoing';
    }

    /* ================= Relations ================= */

    public function renter()
    {
        return $this->belongsTo(User::class, 'renter_id');
    }

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }
}
