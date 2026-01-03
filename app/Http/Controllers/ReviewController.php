<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function addReview(Request $request, $id)
    {
    $user = $request->user();

    if ($user->role !== 'renter') {
        return response()->json(['message' => 'Only renters can add reviews'], 403);
    }

    $booking = Booking::with('apartment')->find($id);

    if (!$booking || $booking->renter_id !== $user->id) {
        return response()->json(['message' => 'Booking not found or unauthorized'], 404);
    }

    if ($booking->end_date > now()->toDateString()) {
        return response()->json(['message' => 'You can only review completed bookings'], 409);
    }

    $data = $request->validate([
        'rating' => 'required|integer|min:1|max:5',
        'review' => 'nullable|string|max:1000',
    ]);

    $booking->rating = $data['rating'];
    $booking->review = $data['review'] ?? null;
    $booking->save();

    return response()->json($booking);
    }
}
