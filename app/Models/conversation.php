<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Message;
use App\Models\Apartment;

class Conversation extends Model
{
    protected $fillable = [
        'apartment_id',
        'renter_id',
        'owner_id'
    ];

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function renter()
    {
        return $this->belongsTo(User::class, 'renter_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
