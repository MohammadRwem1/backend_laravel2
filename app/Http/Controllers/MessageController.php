<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Notifications\NewMessageNotification;

class MessageController extends Controller
{
    public function index(Request $request, $conversationId)
    {
        $conversation = Conversation::with('messages.sender')
            ->findOrFail($conversationId);

        return response()->json($conversation->messages);
    }

    public function store(Request $request, $conversationId)
    {
        $user = $request->user();

        $conversation = Conversation::findOrFail($conversationId);

        if (!in_array($user->id, [$conversation->renter_id, $conversation->owner_id])) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'body' => 'required|string'
        ]);

        $message = $conversation->messages()->create([
            'sender_id' => $user->id,
            'body' => $data['body']
        ]);

        $receiver = $user->id === $conversation->renter_id
            ? $conversation->owner
            : $conversation->renter;

        $receiver->notify(new NewMessageNotification($message));

        return response()->json($message);
    }
}
