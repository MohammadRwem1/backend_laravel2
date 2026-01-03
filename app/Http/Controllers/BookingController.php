<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Apartment;
use Illuminate\Http\Request;
use App\Notifications\BookingStatusNotification;

class BookingController extends Controller
{
    public function store(Request $request, $apartmentId)
    {
        $user = $request->user();

        if ($user->role !== 'renter') {
            return response()->json(['message' => 'Only renters can create bookings.'], 403);
        }

        $validated = $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date'   => 'required|date|after:start_date',
        ]);

        $apartment = Apartment::find($apartmentId);

        if (!$apartment) {
            return response()->json(['message' => 'Apartment not found.'], 404);
        }

        $hasOverlap = Booking::where('apartment_id', $apartment->id)
            ->where('status', 'approved')
            ->where('start_date', '<', $validated['end_date'])
            ->where('end_date', '>', $validated['start_date'])
            ->exists();

        if ($hasOverlap) {
            return response()->json(['message' => 'Apartment is already booked for this period.'], 409);
        }

        $booking = Booking::create([
            'renter_id'    => $user->id,
            'apartment_id' => $apartment->id,
            'start_date'   => $validated['start_date'],
            'end_date'     => $validated['end_date'],
            'status'       => 'pending',
        ]);

        return response()->json([
            'message' => 'Booking request submitted and waiting for owner approval.',
            'data' => $booking
        ], 201);
    }

    public function myBookings(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'renter') {
            return response()->json(['message' => 'Only renters can view their bookings.'], 403);
        }

        $bookings = Booking::with('apartment')
            ->where('renter_id', $user->id)
            ->orderByDesc('start_date')
            ->get();

        return response()->json([
            'message' => 'Your bookings list.',
            'data' => $bookings
        ]);
    }

    public function ownerPending(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'owner') {
            return response()->json(['message' => 'Only owners can view pending bookings.'], 403);
        }

        $bookings = Booking::with(['apartment', 'renter'])
            ->whereHas('apartment', fn ($q) => $q->where('owner_id', $user->id))
            ->where('status', 'pending')
            ->get();

        return response()->json([
            'message' => 'Pending booking requests.',
            'data' => $bookings
        ]);
    }

    public function approve(Request $request, $id)
    {
    $user = $request->user();
    $booking = Booking::with('apartment')->find($id);

    if (! $booking) {
        return response()->json(['message' => 'Booking not found'], 404);
    }

    if ($user->role !== 'owner' || $booking->apartment->owner_id !== $user->id) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    $start = $booking->start_date;
    $end   = $booking->end_date;

    $overlap = Booking::where('apartment_id', $booking->apartment_id)
        ->where('status', 'approved')
        ->where('id', '!=', $booking->id)
        ->where(function ($q) use ($start, $end) {
            $q->where('start_date', '<=', $end)
            ->where('end_date', '>=', $start);
        })
        ->exists();

    if ($overlap) {
        return response()->json(['message' => 'Overlapping approved booking exists'], 409);
    }

    $booking->status = 'approved';
    $booking->save();

    Booking::where('apartment_id', $booking->apartment_id)
        ->where('status', 'pending')
        ->where('id', '!=', $booking->id)
        ->where(function ($q) use ($start, $end) {
            $q->where('start_date', '<=', $end)
            ->where('end_date', '>=', $start);
        })
        ->update(['status' => 'rejected']);
        $booking->update(['status' => 'approved']);

        $renter = $booking->renter;

        $renter->notify(
        new BookingStatusNotification(
        $booking,
        'Your booking has been approved'
            )
        );

    return response()->json($booking);
    }


    public function reject(Request $request, $id)
    {
        $user = $request->user();

        $booking = Booking::with('apartment')->find($id);

        if (!$booking) {
            return response()->json(['message' => 'Booking not found.'], 404);
        }

        if ($user->role !== 'owner' || $booking->apartment->owner_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $booking->update(['status' => 'rejected']);

        return response()->json([
            'message' => 'Booking rejected.',
            'data' => $booking
        ]);
    }

    public function cancel(Request $request, $id)
    {
    $user = $request->user();

    if ($user->role !== 'renter') {
        return response()->json(['message' => 'Only renters can cancel bookings.'], 403);
    }

    $booking = Booking::with('apartment.owner')->find($id);

    if (!$booking || $booking->renter_id !== $user->id) {
        return response()->json(['message' => 'Booking not found or unauthorized.'], 404);
    }

    if ($booking->status === 'approved' && $booking->start_date <= now()->toDateString()) {
        return response()->json(['message' => 'Cannot cancel an active booking.'], 409);
    }

    $booking->update(['status' => 'cancelled']);

    $owner = $booking->apartment->owner;

    if ($owner) {
        $owner->notify(
            new BookingStatusNotification(
                $booking,
                'The renter cancelled the booking'
            )
        );
    }

    return response()->json([
        'message' => 'Booking cancelled successfully.',
        'data' => $booking
    ]);
    }


    public function updateBooking(Request $request, $id)
    {
    $user = $request->user();

    if ($user->role !== 'renter') {
        return response()->json(['message' => 'Only renters can update bookings.'], 403);
    }

    $booking = Booking::find($id);

    if (!$booking || $booking->renter_id !== $user->id) {
        return response()->json(['message' => 'Booking not found or unauthorized.'], 404);
    }

    if ($booking->status !== 'pending') {
        return response()->json(['message' => 'Only pending bookings can be updated.'], 409);
    }

    $validated = $request->validate([
        'start_date' => 'required|date|after_or_equal:today',
        'end_date'   => 'required|date|after:start_date',
    ]);

    $hasOverlap = Booking::where('apartment_id', $booking->apartment_id)
        ->where('status', 'approved')
        ->where('id', '!=', $booking->id)
        ->where('start_date', '<', $validated['end_date'])
        ->where('end_date', '>', $validated['start_date'])
        ->exists();

    if ($hasOverlap) {
        return response()->json(['message' => 'Updated dates conflict with an approved booking.'], 409);
    }

    $booking->update($validated);

    return response()->json([
        'message' => 'Booking updated successfully.',
        'data' => $booking
    ]);
    }


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
