<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller {

/**
 * Display the message list for a particular user or all users. This is build to have 1-to-1 conversation.
 * orWhere() method is used to get messages in both directions, by both sender and receiver.
 * orderBy(created_at', 'atc') method - it returns the oldest messages first to the newest. Based of the in migration $table->timestamp();
 * get() retrieves all matching records from the database as a collection, using collect() method..
 *
 * @param int|null $user_id The ID of the receiver user. If not provided, it will show all messages.
 * 
 * @return \Illuminate\View\View The view for the message index with the users list that users can choose who they want to chat with.
 */
    public function index($user_id = null) {
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


/**
 * Store a new message and handle file uploads.
 * Problem is that, user cn send content alone, but can send files alone without content.
 *
 * @param \Illuminate\Http\Request $request The incoming request containing message data and files.
 * 
 * @return \Illuminate\Http\JsonResponse The response containing the created message and uploaded file URLs.
 */
    public function store(Request $request) {
        $validatedData = $request->validate([
            'content' => 'nullable|string|max:1000',
            'files.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,docx,txt|max:2048',
            'receiver_id' => 'required|exists:users,id',
        ]);
    
        $receiver = User::findOrFail($validatedData['receiver_id']);
    
        $message = Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $receiver->id,
            'content' => $validatedData['content'],
        ]);
    
        $filesData = [];

        //$request->hasFile('files'), checks if the file input names 'files' contains any files.
        //It makes sure that user has actually uploaded files before proceeding.
        //It returns the bool 'true', otherwise false.

        //$request->file('files') as $file, the loop through each file that the user uploaded(files can be an array if multiple files are selected).
        //It uploads and returns them as an array, which is then looped over to process each file individually.

        //store() method saves uploaded file(es).
        //First parameter 'messages' is the folder in which the file will be stored. public/storage/messages.
        //Second parameter 'public' tells Laravel to store the file in the public disk.

        //getClientOriginalName() - This retrieves the original name of the uploaded file (as it was on the user’s local machine).
        //Storage::disk('public') accesses the public disk where files are stored. The url() method generates the public url.
    
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
    
    
/**
 * Display the message conversation with a specific user.
 *
 * @param int $user_id The ID of the receiver user to show the conversation with.
 * 
 * @return \Illuminate\View\View The view showing the messages between the logged-in user and the receiver.
 */
    public function show($user_id) {
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


/**
 * Edit a specific message.
 *
 * @param int $message_id The ID of the message to be edited.
 * 
 * @return \Illuminate\Http\JsonResponse The message data in JSON format for editing.
 */
    public function edit($message_id) {
        $message = Message::findOrFail($message_id);
        return response()->json([
            'message' => $message
        ]);
    }


/**
 * Update an specific message
 *
 * @param \Illuminate\Http\Request $request The incoming request containing the updated content.
 * @param int $message_id The ID of the message to be updated.
 * 
 * @return \Illuminate\Http\JsonResponse The updated message data in JSON format.
 */
    public function update(Request $request, $message_id) {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $message = Message::findOrFail($message_id);
        $message->content = $request->content;
        $message->save();

        return response()->json([
            'message' => $message
        ]);
    }


/**
 * Delete a specific message.
 *
 * @param int $messageId The ID of the message to be deleted.
 * 
 * @return \Illuminate\Http\Response Reloads the page after deletion process.
 */
    public function destroy($messageId) {
        $message = Message::find($messageId);
        if ($message->sender_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        $message->delete();
    }
}
