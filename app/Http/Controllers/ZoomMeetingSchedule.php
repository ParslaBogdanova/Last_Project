<?php

namespace App\Http\Controllers;

use App\Models\ZoomMeeting;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Day;
use Illuminate\Support\Facades\Auth;

class ZoomMeetingSchedule extends Controller
{

   
    public function create($month, $year, $day_id)
    {
        $day = Day::findOrFail($day_id);

        if ($day->calendar->user_id !== Auth::id() || $day->calendar->month != $month || $day->calendar->year != $year){
            abort(403, 'Unauthorized action.');
        }
        $users = User::where('id', '!=', Auth::id())->get();

        return view('zoomMeetings.create', [
            'day' => $day,
            'month' => $month,
            'year' => $year,
            'users' => $users,
        ]);
    }

    
    public function store(Request $request, $month, $year, $day_id)
    {

      
        $day = Day::findOrFail($day_id);
    
        if ($day->calendar->user_id !== Auth::id() || $day->calendar->month != $month || $day->calendar->year != $year) {
            return response()->json(['error' => 'Unauthorized action.'], 403);
        }
    
        $request->validate([
            'title_zoom' => 'required|string',
            'topic_zoom' => 'nullable|string',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'invited_users' => 'required|array',
            'invited_users.*' => 'exists:users,id',
        ]);
    
        $zoomMeeting = ZoomMeeting::create([
            'title_zoom' => $request->input('title_zoom'),
            'topic_zoom' => $request->input('topic_zoom'),
            'start_time' => $request->input('start_time'),
            'end_time' => $request->input('end_time'),
            'user_id' => Auth::id(),
            'day_id' => $day->id,
        ]);
    
        $zoomMeeting->users()->attach($request->input('invited_users'));

        return redirect()->route('calendar.show', [
            'month' => $month,
            'year' => $year,
            'day_id' => $day_id,
        ]);
        dd($request->all());
    }

    public function edit($month, $year, $day_id, $id)
    {
        $zoomMeeting = ZoomMeeting::findOrFail($id);

        if ($zoomMeeting->user_id !== Auth::id() || $zoomMeeting->day_id != $day_id) {
            abort(403, 'Unauthorized action.');
        }

        return view('zoomMeeting.edit', [
            'zoomMeeting' => $zoomMeeting,
            'month' => $month,
            'year' => $year,
            'day_id' => $day_id,
        ]);
    }

    
    public function update(Request $request, ZoomMeeting $zoomMeeting)
    {
        $validatedData = $request->validate([
            'zoom_meeting_id' => 'required|exists:zoom_meeting,id',
            'title_zoom' => 'required|string|max:255',
            'topic_zoom' => 'nullable|string',
            'invited_users' => 'required|array',
            'invited_users.*' => 'exists:users,id',
            'start_time' => 'required|date_format:H:i:s',
            'end_time' => 'nullable|date_format:H:i:s',
        ]);
    
        $zoomMeeting = ZoomMeeting::findOrFail($validatedData['zoom_meeting_id']);
        $zoomMeeting->update([
            'title_zoom' => $validatedData['title'],
            'topic_zoom' => $validatedData['topic'],
            'invited_users' => $validatedData['invited_users'],
            'start_time' => $validatedData['start_time'],
            'end_time' => $validatedData['end_time'],
        ]);
    
        return redirect()->route('calendar.show', ['month' => $month, 'year' => $year, 'day_id' => $day_id]);
    }

    
    public function destroy($month, $year, $day_id, $zoom_meeting_id)
    {
        $zoomMeeting = ZoomMeeting::find($zoom_meeting_id);
    
        if ($zoomMeeting) {
            $zoomMeeting->delete();
            return response()->json(['message' => 'Zoom Meeting deleted successfully.'], 200);
        }
    
        return response()->json(['message' => 'Zoom Meeting not found.'], 404);
    }
}
