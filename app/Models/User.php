<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\OneTimePasswords\OneTimePassword;
use App\Models\FcmToken;
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public function sendOneTimePassword()
    {
        $otp = OneTimePassword::create($this, 6, 300);
        $this->notify(new \App\Notifications\SendOtpNotification($otp->token));
        return $otp->token;
    }

    public function attemptLoginUsingOneTimePassword($otp)
    {
        return OneTimePassword::consume($this, $otp);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'password',
        'role',
        'birth_date',
        'id_image',
        'profile_image'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function apartments(){
        return $this->hasMany(Apartment::class, 'owner_id', 'id');
    }

    public function bookings()
    {
    return $this->hasMany(Booking::class);  
    }


public function fcmTokens()
{
    return $this->hasMany(FcmToken::class);
}

}
