<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Apartment;
use Illuminate\Http\Request;

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
            'tenant_id'    => $user->id,
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
            ->where('tenant_id', $user->id)
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

        $bookings = Booking::with(['apartment', 'tenant'])
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

        if (!$booking) {
            return response()->json(['message' => 'Booking not found.'], 404);
        }

        if ($user->role !== 'owner' || $booking->apartment->owner_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $hasOverlap = Booking::where('apartment_id', $booking->apartment_id)
            ->where('status', 'approved')
            ->where('id', '!=', $booking->id)
            ->where('start_date', '<', $booking->end_date)
            ->where('end_date', '>', $booking->start_date)
            ->exists();

        if ($hasOverlap) {
            return response()->json(['message' => 'Cannot approve due to overlapping approved booking.'], 409);
        }

        $booking->update(['status' => 'approved']);

        return response()->json([
            'message' => 'Booking approved successfully.',
            'data' => $booking
        ]);
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

        $booking = Booking::find($id);

        if (!$booking || $booking->tenant_id !== $user->id) {
            return response()->json(['message' => 'Booking not found or unauthorized.'], 404);
        }

        if ($booking->status === 'approved' && $booking->start_date <= now()->toDateString()) {
            return response()->json(['message' => 'Cannot cancel an active booking.'], 409);
        }

        $booking->update(['status' => 'cancelled']);

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

        if (!$booking || $booking->tenant_id !== $user->id) {
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
}
