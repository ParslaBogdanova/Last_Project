<?php

namespace App\Http\Controllers;

use App\Models\ZoomMeeting;
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

        return view('zoomMeeting.create', [
            'day' => $day,
            'month' => $month,
            'year' => $year,
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

    
    public function show(ZoomMeeting $zoomMeeting)
    {
        //
    }

  
    public function edit(ZoomMeeting $zoomMeeting)
    {
        //
    }

    
    public function update(Request $request, ZoomMeeting $zoomMeeting)
    {
        //
    }

    
    public function destroy(ZoomMeeting $zoomMeeting)
    {
        //
    }
}
