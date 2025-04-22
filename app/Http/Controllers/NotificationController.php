<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

use App\Models\ReminderZoomMeeting;
use App\Models\ZoomMeeting;

class NotificationController extends Controller {
    public function sendNotification($zoomMeetingId, $userIds, $message) {
        $zoomMeeting = ZoomMeeting::findOrFail($zoomMeetingId);
    
        foreach ($userIds as $userId) {
            Notification::create([
                'user_id' => $userId, // Only invited users
                'zoom_meetings_id' => $zoomMeeting->id,
                'message' => $message,
            ]);
        }
    }



    public function index() {
        $userId = Auth::id();
        
        $notifications = Notification::where('user_id', Auth::id())
        ->with('zoomMeeting', 'user') 
        ->get();

        $reminders = ReminderZoomMeeting::where('user_id', $userId)
        ->with('zoomMeeting')
        ->get();


        return view('tasks.index',[
            'notifications' => $notifications,
            'reminders' => $reminders,
        ]);
    }
}
