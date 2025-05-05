<?php

namespace App\Http\Controllers;

use App\Models\ZoomMeeting;
use App\Models\ZoomCall;
use App\Models\BlockedDays;
use App\Models\Notification;
use App\Models\ReminderZoomMeeting;
use App\Models\User;
use App\Models\Day;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ZoomMeetingController extends Controller {

/**
 * Display the form to create a new Zoom meeting. After choosing a specific date.
 * 
 * Retrieves the calendar `Day` instance for the provided date,
 * and also fetches a list of users(including the 'Test User')
 * to populate the invited users list.
 *
 * @param  int  $month  The month being viewed in the calendar
 * @param  int  $year   The year being viewed in the calendar
 * @param  string  $date  The date the meeting is to be created for (Y-m-d)
 * 
 * @return \Illuminate\View\View  The view for creating a Zoom meeting
 */   
    public function create($month, $year, $date) {
        $day = Day::firstOrFail($date);
        $users = User::where('id', '!=', Auth::id())->get();

        return view('zoom_meetings.create', [
            'day' => $day,
            'month' => $month,
            'year' => $year,
            'users' => $users,
        ]);
    }


/**
 * Store a newly created Zoom meeting in the database.
 * But also checks the availability of the invited users,
 * checkUserAvailability() is used as a argument in store function.
 * Along with scheduleReminders() & sendZoomMeetingNotification(), & ZoomCallData. Those only are activated when zoom meeting is successfully created.
 * 
 *
 * @param  \Illuminate\Http\Request  $request
 * @param  int  $month
 * @param  int  $year
 * @param  string  $date
 * 
 * @return \Illuminate\Http\RedirectResponse
 */
    public function store(Request $request, $month, $year, $date) { 
        $day = Day::where('date', $date)
        ->whereHas('calendar', function ($query) {
            $query->where('user_id', Auth::id());
        })
        ->firstOrFail();
    
        $validatedData = $request->validate([
            'title_zoom' => 'required|string',
            'topic_zoom' => 'nullable|string',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'invited_users' => 'required|array',
            'invited_users.*' => 'exists:users,id',
        ]);

        $invitedUsers = $validatedData['invited_users'];
        $date = $day->date;
        $start_time = $validatedData['start_time'];
        $end_time = $validatedData['end_time'];
    
        $unavailableUsers = $this->checkUserAvailability($invitedUsers, $date, $start_time, $end_time);
    
        if (!empty($unavailableUsers)) {
            return redirect()->back()
                ->with('error', 'Some users are unavailable for this meeting.')
                ->with('unavailable_users', $unavailableUsers);
        }
        
        $zoomMeeting = ZoomMeeting::create([
            'title_zoom' => $validatedData['title_zoom'],
            'topic_zoom' => $validatedData['topic_zoom'],
            'start_time' => $validatedData['start_time'],
            'end_time' => $validatedData['end_time'],
            'creator_id' => Auth::id(),
            'date'=> $date,
        ]);

        $zoomMeeting->invitedUsers()->attach($validatedData['invited_users'], [
            'date'=>$date,
        ]);

        foreach ($validatedData['invited_users'] as $userId) {
            ZoomCall::create([
                'zoom_meetings_id' => $zoomMeeting->id,
                'user_id' => $userId,
                'status' => 'active',
            ]);
        }

        $this->scheduleReminders($zoomMeeting, $validatedData['invited_users']);
        $this->ZoomCallData($zoomMeeting, $validatedData['invited_users']);
    
        $message = "You have been invited to a Zoom meeting: {$zoomMeeting->title_zoom}";
        $this->sendZoomMeetingNotification($zoomMeeting, $validatedData['invited_users'], $message);
    
        return redirect()->route('calendar.show', [
            'month' => $month,
            'year' => $year,
            'date' => $date,
        ]);
    }
    


/**
 * Display the edit form for an existing Zoom meeting.
 * 
 * Finds the ZoomMeeting by ID and shows a popup windows with the meeting's details.
 * The meeting must exist, otherwise it throws a 404 error.
 * Users have to be invited again, even its the old ones or new ones.
 * 
 * @param  int  $month  The current calendar month
 * @param  int  $year   The current calendar year
 * @param  string  $date  The date associated with the Zoom meeting
 * @param  int  $zoom_meetings_id  The ID of the Zoom meeting to edit
 * 
 * @return \Illuminate\View\View  The view for editing the Zoom meeting
 */
    public function edit($month, $year, $date, $zoom_meetings_id)
    {
        $zoomMeeting = ZoomMeeting::findOrFail($zoom_meetings_id);
        return view('zoom_meetings.edit', [
            'zoomMeeting' => $zoomMeeting,
            'month' => $month,
            'year' => $year,
            'date' => $date,
        ]);
    }



/**
 * Update an existing Zoom meeting's data and invited users.
 * 
 * First validates the request and checks whether the meeting exists and 
 * is associated with the date. Then it ensures the user is authorized.
 * It checks for availability of all users (using checkUserAvailability() asa argument again).
 * 
 * If no conflicts are found, it updates the meeting details and syncs
 * the invited users list. Also sends notifications for added or removed users.
 * For removed users, their notification doesn't send, because their data ID has already been deleted.
 * For new invited users, it sends the same notification and reminder as everyone else in store function.
 *
 * @param  \Illuminate\Http\Request  $request
 * @param  int  $month
 * @param  int  $year
 * @param  string  $date
 * 
 * @return \Illuminate\Http\RedirectResponse
 */
    public function update(Request $request, $month, $year, $date){

        $validateData = $request->validate([
            'zoom_meetings_id' => 'required|exists:zoom_meetings,id',
            'title_zoom' => 'required|string',
            'topic_zoom' => 'nullable|string',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'invited_users' => 'nullable|array',
            'invited_users.*' => 'exists:users,id',
        ]);
    
        $zoomMeeting = ZoomMeeting::where('id', $validateData['zoom_meetings_id'])
                            ->where('date', $date)
                            ->firstOrFail();

        if ($zoomMeeting->creator_id !== Auth::id() && ! $zoomMeeting->invitedUsers->pluck('id')->contains(Auth::id())) {
            return response()->json(['error' => 'Unauthorized action'], 403);
        }
                            
    
        $invitedUsers = $validateData['invited_users'];
        $date = $zoomMeeting->date;
        $start_time = $validateData['start_time'];
        $end_time = $validateData['end_time'];
    
        $unavailableUsers = $this->checkUserAvailability($invitedUsers, $date, $start_time, $end_time, $zoomMeeting->id);
    
        if (!empty($unavailableUsers)) {
            return redirect()->back()
                ->with('error', 'Some users are unavailable for this meeting.')
                ->with('unavailable_users', $unavailableUsers);
        }
    
        $zoomMeeting->update([
            'title_zoom' => $validateData['title_zoom'],
            'topic_zoom' => $validateData['topic_zoom'],
            'start_time' => $validateData['start_time'],
            'end_time' => $validateData['end_time'],
        ]);
    
        $syncData = [];
        foreach ($validateData['invited_users'] as $userId) {
            $syncData[$userId] = [
                'date' => $zoomMeeting->date,
                'updated_at' => now(),
            ];
        }
    
        $zoomMeeting->invitedUsers()->sync($syncData);
    
        $previousUsers = $zoomMeeting->invitedUsers()->pluck('users.id')->toArray();
        $newUsers = array_diff($validateData['invited_users'], $previousUsers);
        $removedUsers = array_diff($previousUsers, $validateData['invited_users']);
    
        if (!empty($newUsers)) {
            $message = "You have been added to a Zoom meeting: {$zoomMeeting->title_zoom}";
            $this->sendZoomMeetingNotification($zoomMeeting, $newUsers, $message);
        }
    
        if (!empty($removedUsers)) {
            $message = "You have been removed from the Zoom meeting: {$zoomMeeting->title_zoom}";
            $this->sendZoomMeetingNotification($zoomMeeting, $removedUsers, $message);
        }
    
        return redirect()->route('calendar.show', [
            'month' => $month,
            'year' => $year,
            'date' => $date,
        ]);
    }



/**
 * Deletes a Zoom meeting and detaches all invited users.
 * 
 * Removes associated user connections from the pivot table,
 * and deletes the meeting. Responds with a JSON message upon success
 * or returns an error message if deletion fails.
 * 
 * Before deletion process, the json message asks the creator if they are sure to delete the meeting. 
 * If the host has approved, then the zoom meeting then deletes it.
 *
 * @param  int  $month
 * @param  int  $year
 * @param  string  $date
 * @param  int  $zoomMeeting_id
 * 
 * @return \Illuminate\Http\JsonResponse
 */
    public function destroy($month, $year, $date, $zoomMeeting_id) {
        $zoomMeeting = ZoomMeeting::find($zoomMeeting_id);

        try {
            $invitedUsers = $zoomMeeting->invitedUsers()->pluck('users.id')->toArray();
            $zoomMeeting->invitedUsers()->detach();
            $zoomMeeting->delete();

            return response()->json(['message' => 'Zoom Meeting deleted successfully.']);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }



/**
 * Sends a notification to a list of users who are part of the  Zoom meeting.
 * 
 * Creates a Notification entry for each user in the provided list
 * except the creator of the meeting.
 *
 * @param  \App\Models\ZoomMeeting  $zoomMeeting
 * @param  array  $userIds  List of user IDs to notify
 * @param  string  $message  Notification content
 * 
 * @return void
 */
    private function sendZoomMeetingNotification($zoomMeeting, $userIds, $message) {
        foreach ($userIds as $userId) {
            if ($userId !== $zoomMeeting->creator_id) {
                Notification::create([
                    'user_id' => $userId,
                    'zoom_meetings_id' => $zoomMeeting->id,
                    'message' => $message,
                ]);
            }
            
        }
    }




/**
 * Checks whether the invited users are available for the proposed meeting time.
 * 
 * This includes checking for blocked days (like vacations or personal events),
 * and any existing Zoom meetings that would overlap with the new meeting’s time.
 * 
 * If `zoom_meeting_id` is provided, it excludes that meeting from conflict checking
 * (useful during an update operation and create operation process.).
 * 
 * If someone is unavailable, then $conflictingMeeting(checks the other meetings time)
 * & $blockedDay(checks if any of the invited users have blocked days created) is places in if statement,
 * that gives in return $unavailableUsers[] array of user names and their unavailable reasons.
 *
 * @param  array  $invitedUsers
 * @param  string  $date
 * @param  string  $start_time
 * @param  string  $end_time
 * @param  int|null  $zoom_meeting_id  Optional meeting ID to exclude from check
 * 
 * @return array  List of users who are unavailable with reasons
 */
    public function checkUserAvailability($invitedUsers, $date, $start_time, $end_time, $zoom_meeting_id = null) {
        $unavailableUsers = [];

        foreach ($invitedUsers as $userId) {
            $user = User::find($userId);
            $blockedDay = BlockedDays::where('user_id', $userId)
                                    ->where('date', $date)
                                    ->first();

            if ($blockedDay) {
                $unavailableUsers[] = [
                    'name' => $user->name,
                    'reason' => "is unavailable due to: {$blockedDay->reason}. Please choose a different date.",
                ];
                continue;
            }

            $conflictingMeeting = ZoomMeeting::whereHas('invitedUsers', function ($query) use ($userId) {
                $query->where('users.id', $userId);
            })
            ->where('date', $date)
            ->where(function ($query) use ($start_time, $end_time) {
                $query->whereBetween('start_time', [$start_time, $end_time])
                    ->orWhereBetween('end_time', [$start_time, $end_time])
                    ->orWhere(function ($query) use ($start_time, $end_time) {
                        $query->where('start_time', '<', $start_time)
                                ->where('end_time', '>', $end_time);
                    });

                $query->orWhere(function ($query) use ($start_time, $end_time) {
                    $query->where('start_time', '>', $end_time)
                        ->where('end_time', '<', $start_time);
                });
            })
            ->where('id', '<>', $zoom_meeting_id)
            ->first();

            if ($conflictingMeeting) {
                $unavailableUsers[] = [
                    'name' => $user->name,
                    'reason' => "is unavailable due to another Zoom meeting from {$conflictingMeeting->start_time} till {$conflictingMeeting->end_time}. Please select a different date or time.",
                ];
            }
        }

        return $unavailableUsers;
    }


/**
 * Schedules reminder entries for the creator and all invited users of a Zoom meeting.
 * 
 * These entries are stored in `ReminderZoomMeeting` and marked as unseen initially.
 * 
 * @param  \App\Models\ZoomMeeting  $zoomMeeting
 * @param  array  $invitedUsers  List of user IDs
 * 
 * @return void
 */
    private function scheduleReminders(ZoomMeeting $zoomMeeting, array $invitedUsers) {

        ReminderZoomMeeting::create([
            'user_id' => $zoomMeeting->creator_id,
            'zoom_meetings_id' => $zoomMeeting->id,
            'seen' => false,
        ]);

        foreach ($invitedUsers as $userId) {
            ReminderZoomMeeting::create([
                'user_id' => $userId,
                'zoom_meetings_id' => $zoomMeeting->id,
                'seen' => false,
            ]);
        }
    }



/**
 * Creates or updates Zoom call entries for each user invited to a Zoom meeting.
 *  This includes the meeting creator. Status is always set to 'active'.
 *
 * @param  \App\Models\ZoomMeeting  $zoomMeeting
 * @param  array  $invitedUsers  List of invited user IDs
 * 
 * @return void
 */
    public function ZoomCallData(ZoomMeeting $zoomMeeting, array $invitedUsers) {
        ZoomCall::updateOrCreate(
            ['user_id' => $zoomMeeting->creator_id, 'zoom_meetings_id' => $zoomMeeting->id],
            ['status' => 'active']
        );

        foreach ($invitedUsers as $userId) {
            ZoomCall::updateOrCreate(
                ['user_id' => $userId, 'zoom_meetings_id' => $zoomMeeting->id],
                ['status' => 'active']
            );
        }
    }
}