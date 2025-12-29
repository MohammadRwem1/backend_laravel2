<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Apartment extends Model
{
    use HasFactory;
    
    protected $fillable=[
        'title' ,    
        'governorate',
        'city'   ,     
        'number_rooms',
        'description' ,
        'price'     ,  
        'main_image' , 
        'images.*' ,   
    ];

    public function owner(){
        return $this->belongsTo(User::class,'owner_id','id');
    }
    public function bookings()
    {
    return $this->hasMany(Booking::class);
    }
}
