<?php

namespace App\Http\Controllers;

use App\Models\ZoomMeeting;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Day;
use Illuminate\Support\Facades\Auth;

class ZoomMeetingSchedule extends Controller
{
    
    public function index()
    {
        return view('zoomMeeting.index');
    }

   
    public function create($month, $years, $day_id)
    {
        $day = Day::findOrFail($day_id);

        if ($day->calendar_user_id !== Auth::id() || $day->calendar->month != $month || $day->calendar_year != $year){
            abort(403, 'Unauthorized action.');
        }
        $users = User::where('id', '!=', Auth::id())->get();

        return view('zoomMeeting.create', [
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
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'topic' => 'nullable|string',
            'invited_users' => 'required|array',
            'invited_users.*' => 'exists:users,id',
            'start_time' => 'required|date_format:Y-m-d H:i:s',
            'end_time' => 'nullable|date_format:Y-m-d H:i:s',
        ]);

        $zoomMeeting = ZoomMeeting::create([
            'title' => $request->input('title'),
            'topic' => $request->input('topic'),
            'invited_users' => json_encode($request->input('invited_users')),
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
    }

    public function show($month, $year, $day_id)
    {
        $day = Day::findOrFail($day_id);
    
        // Show the day details, no need to load users here anymore
        return view('calendar.show', compact('day', 'month', 'year'));
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
            'title' => 'required|string|max:255',
            'topic' => 'nullable|string',
            'invited_users' => 'required|array',
            'invited_users.*' => 'exists:users,id',
            'start_time' => 'required|date_format:Y-m-d H:i:s',
            'end_time' => 'nullable|date_format:Y-m-d H:i:s',
        ]);
    
        $zoomMeeting = ZoomMeeting::findOrFail($validatedData['zoom_meeting_id']);
        $zoomMeeting->update([
            'title' => $validatedData['title'],
            'topic' => $validatedData['topic'],
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
