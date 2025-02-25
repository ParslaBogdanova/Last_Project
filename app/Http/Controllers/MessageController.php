<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    public function index($user_id = null)
    {
        $user = Auth::user();
        $users = User::where('id', '!=', $user->id)->get();
        $receiver_id = $user_id;
        $messages = collect();

        if ($receiver_id) {
            $messages = Message::where(function ($query) use ($user, $receiver_id) {
                $query->where('sender_id', $user->id)->where('receiver_id', $receiver_id);
            })->orWhere(function ($query) use ($user, $receiver_id) {
                $query->where('sender_id', $receiver_id)->where('receiver_id', $user->id);
            })->orderBy('created_at', 'asc')->get();
        }

        return view('messages.index', [
            'users'=>$users,
            'receiver_id'=>$receiver_id,
            'messages' => $messages,
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'content' => 'nullable|string|max:1000',
            'files.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,docx,txt|max:2048',
            'receiver_id' => 'required|exists:users,id',
        ]);
    
        $receiver = User::findOrFail($validatedData['receiver_id']);
        
        if (!$validatedData['content'] && !$request->hasFile('files')) {
            return response()->json(['error' => 'Message must contain text or a file.'], 422);
        }
    
        $message = Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $receiver->id,
            'content' => $validatedData['content'],
        ]);
    
        $filesData = [];
    

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $filePath = $file->store('messages', 'public'); 
                $fileTitle = $file->getClientOriginalName();
    
                $messageFile = $message->files()->create([
                    'file_path' => $filePath,
                    'file_title' => $fileTitle,
                ]);
    
                $filesData[] = [
                    'file_url' => Storage::disk('public')->url($messageFile->file_path),
                    'file_name' => $messageFile->file_title,
                ];
            }
        }
    
        return response()->json([
            'message' => $message,
            'files' => $filesData,
        ]);
    }
    
    


public function show($user_id)
{
    $user = Auth::user();
    $users = User::where('id', '!=', $user->id)->get();
    $receiver_id = $user_id;

    $messages = Message::where(function ($query) use ($user, $receiver_id) {
        $query->where('sender_id', $user->id)->where('receiver_id', $receiver_id);
    })->orWhere(function ($query) use ($user, $receiver_id) {
        $query->where('sender_id', $receiver_id)->where('receiver_id', $user->id);
    })->orderBy('created_at', 'asc')->get();


    $messages->each(function ($message) {
        if ($message->file_path) {
            $message->file_url = Storage::url($message->file_path);
            $message->file_name = $message->file_title;
        }
    });

    return view('messages.show', [
        'users'=>$users,
        'receiver_id'=>$receiver_id,
        'messages' => $messages
    ]);
}



    public function edit($message_id)
    {
        $message = Message::findOrFail($message_id);

        if ($message->sender_id !== Auth::id()) {
            return response()->json(['error' => 'You can only edit your own messages.'], 403);
        }

        return response()->json(['message' => $message]);
    }




    public function update(Request $request, $message_id)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $message = Message::findOrFail($message_id);

        if ($message->sender_id !== Auth::id()) {
            return response()->json(['error' => 'You can only edit your own messages.'], 403);
        }

        $message->content = $request->content;
        $message->save();

        return response()->json(['message' => $message]);
    }



    
    public function destroy($messageId)
    {
        $message = Message::find($messageId);

        if (!$message || $message->sender_id != auth()->id()) {
            return response()->json(['error' => 'Message not found or unauthorized'], 404);
        }

        $message->delete();

        return response()->json(['success' => true, 'message' => 'Message deleted successfully.']);
    }
}
