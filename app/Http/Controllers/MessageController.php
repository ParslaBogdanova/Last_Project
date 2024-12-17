<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    /**
     * Show the messages page with contacts.
     */
    public function index()
    {
        $user = Auth::user();
        // Get all users excluding the authenticated user
        $contacts = User::where('id', '!=', $user->id)->get();
        return view('messages.index', compact('contacts'));
    }

    /**
     * Get the chat history between the authenticated user and a specific contact.
     */
    public function getChatHistory($userId)
    {
        $user = Auth::user();
        $contact = User::findOrFail($userId);

        // Get the messages exchanged between the authenticated user and the selected contact
        $messages = Message::where(function ($query) use ($user, $contact) {
            $query->where('sender_id', $user->id)
                ->where('receiver_id', $contact->id);
        })
        ->orWhere(function ($query) use ($user, $contact) {
            $query->where('sender_id', $contact->id)
                ->where('receiver_id', $user->id);
        })
        ->orderBy('created_at')
        ->get();

        return response()->json([
            'contact' => $contact,
            'messages' => $messages->map(function($message) use ($user) {
                return [
                    'content' => $message->content,
                    'is_sender' => $message->sender_id == $user->id
                ];
            })
        ]);
    }

    /**
     * Send a new message from the authenticated user to a specific contact.
     */
    public function sendMessage(Request $request)
    {
        $user = Auth::user();

        // Validate the message and contact_id
        $validated = $request->validate([
            'contact_id' => 'required|exists:users,id',
            'message' => 'required|string|max:500'
        ]);

        $contact = User::findOrFail($validated['contact_id']);

        // Create a new message record
        $message = Message::create([
            'sender_id' => $user->id,
            'receiver_id' => $contact->id,
            'content' => $validated['message']
        ]);

        return response()->json([
            'message' => $message->content,
            'is_sender' => true
        ]);
    }
}
