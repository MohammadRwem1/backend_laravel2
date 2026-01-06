<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Twilio\Rest\Client;

class SendOtpNotification extends Notification
{
    use Queueable;

    public $otp;

    public function __construct($otp)
    {
        $this->otp = $otp;
    }

    public function via($notifiable)
    {
        return ['sms'];
    }

    public function toSms($notifiable)
    {
        $sid = env('TWILIO_SID');
        $token = env('TWILIO_AUTH_TOKEN');
        $from = env('TWILIO_FROM');
        $client = new Client($sid, $token);
        $client->messages->create($notifiable->phone, [
            'from' => $from,
            'body' => "Your OTP code is: {$this->otp}"
        ]);
    }
}
