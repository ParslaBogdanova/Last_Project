<?php

namespace App\Http\Controllers;

use App\Models\ZoomMeeting;
use App\Models\BlockedDays;
use App\Models\Notification;
use App\Models\ReminderZoomMeeting;
use App\Models\User;
use App\Models\Day;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ZoomMeetingController extends Controller
{

    public function create($month, $year, $date)
    {
        $day = Day::firstOrFail($date);


        if ($day->calendar->user_id !== Auth::id() || $day->calendar->month != $month || $day->calendar->year != $year){
            abort(403, 'Unauthorized action.');
        }
        $users = User::where('id', '!=', Auth::id())->get();

        return view('zoom_meetings.create', [
            'day' => $day,
            'month' => $month,
            'year' => $year,
            'users' => $users,
        ]);
    }

    public function store(Request $request, $month, $year, $date)
    { 
        $day = Day::where('date', $date)
        ->whereHas('calendar', function ($query) {
            $query->where('user_id', Auth::id());
        })
        ->firstOrFail();
    
        if ($day->calendar->user_id !== Auth::id() || $day->calendar->month != $month || $day->calendar->year != $year) {
            abort(403, 'Unauthorized action.');
        }
    
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

        $this->scheduleReminders($zoomMeeting, $validatedData['invited_users']);
    
        $message = "You have been invited to a Zoom meeting: {$zoomMeeting->title_zoom}";
        $this->sendZoomMeetingNotification($zoomMeeting, $validatedData['invited_users'], $message);
    
        return redirect()->route('calendar.show', [
            'month' => $month,
            'year' => $year,
            'date' => $date,
        ]);
    }
    

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


public function destroy($month, $year, $date, $zoomMeeting_id)
{
    $zoomMeeting = ZoomMeeting::find($zoomMeeting_id);

    try {

        $invitedUsers = $zoomMeeting->invitedUsers()->pluck('users.id')->toArray();

        $zoomMeeting->invitedUsers()->detach();
        $zoomMeeting->delete();

        return response()->json(['message' => 'Zoom Meeting deleted successfully.'], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}


private function sendZoomMeetingNotification($zoomMeeting, $userIds, $message)
{
    \Log::info('Sending Zoom Meeting Cancellation Notifications', [
        'zoomMeeting' => $zoomMeeting->id,
        'userIds' => $userIds,
        'message' => $message,
    ]);

    foreach ($userIds as $userId) {
        if ($userId !== $zoomMeeting->creator_id) {
            \Log::info('Sending notification', ['user_id' => $userId, 'zoom_meeting_id' => $zoomMeeting->id]);

            Notification::create([
                'user_id' => $userId,
                'zoom_meetings_id' => $zoomMeeting->id,
                'message' => $message,
            ]);
        }
        
    }
}


public function checkUserAvailability($invitedUsers, $date, $start_time, $end_time, $zoom_meeting_id = null)
{
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

private function scheduleReminders(ZoomMeeting $zoomMeeting, array $invitedUsers)
{

    $reminderTime = Carbon::parse($zoomMeeting->start_time)->subMinutes(5);
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
}
