<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class BookingStatusNotification extends Notification
{
    public function __construct(
        public $booking,
        public string $message
    ) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'booking_id' => $this->booking->id,
            'status'     => $this->booking->status,
            'message'    => $this->message,
        ];
    }
}
