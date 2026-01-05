<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Models\Apartment;

class ConversationController extends Controller
{
    public function store(Request $request, Apartment $apartment)
    {
        $user = $request->user();

        if ($user->role !== 'renter') {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        
        try {
            $conversation = Conversation::firstOrCreate(
                [
                    'apartment_id' => $apartment->id,
                    'renter_id' => $user->id
                ],
                [
                    'owner_id' => $apartment->owner_id
                ]
            );
        } catch (\Exception $e) {
            return response()->json(['message' => 'Conversation already exists'], 409);
        }

        return response()->json($conversation);
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $conversations = Conversation::where(function($q) use ($user) {
                $q->where('renter_id', $user->id)
                ->orWhere('owner_id', $user->id);
            })
            ->with('apartment')
            ->latest()
            ->get();

        return response()->json($conversations);
    }
}
