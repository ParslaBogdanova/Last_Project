<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

use App\Models\ReminderZoomMeeting;
use App\Models\ZoomMeeting;

class NotificationController extends Controller {

/**
 * Send notifications to users regarding a specific Zoom meeting.
 *
 * This method creates notifications for multiple users about a specific Zoom meeting.
 * Each notification contains the Zoom meeting's ID and a custom message, along with a title of the zoom meeting and the creators name.
 *
 * @param int $zoomMeetingId The ID of the Zoom meeting for which the notifications are sent.
 * @param array $userIds An array of user IDs to whom the notification will be sent.
 * @param string $message The message content for the notification.
 * 
 * @return void
 */
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


/**
 * Display a list of notifications and reminders for the logged-in user.
 * Reminders are meant to all users + invited users.
 *
 * This method retrieves all notifications and reminders for the logged-in user.
 * It includes notifications related to Zoom meetings and reminders for upcoming Zoom meetings.
 *
 * @return \Illuminate\View\View The view containing the notifications and reminders for the user.
 */
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
