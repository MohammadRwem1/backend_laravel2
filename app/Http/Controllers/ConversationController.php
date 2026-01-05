<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Models\Apartment;

class ConversationController extends Controller
{
    public function store(Request $request, $apartmentId)
    {
        $user = $request->user();

        if ($user->role !== 'renter') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $apartment = Apartment::findOrFail($apartmentId);

        $conversation = Conversation::firstOrCreate(
            [
                'apartment_id' => $apartment->id,
                'renter_id' => $user->id
            ],
            [
                'owner_id' => $apartment->owner_id
            ]
        );

        return response()->json($conversation);
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $conversations = Conversation::where('renter_id', $user->id)
            ->orWhere('owner_id', $user->id)
            ->with('apartment')
            ->latest()
            ->get();

        return response()->json($conversations);
    }
}
