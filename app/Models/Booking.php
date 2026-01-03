<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
    ];

    public function renter()
    {
        return $this->belongsTo(User::class, 'renter_id');
    }

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }
}
