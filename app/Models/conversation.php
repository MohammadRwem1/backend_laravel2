<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
