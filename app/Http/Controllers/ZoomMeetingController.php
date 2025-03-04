<?php

namespace App\Http\Controllers;

use App\Models\ZoomMeeting;
use App\Models\User;
use App\Models\Day;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ZoomMeetingController extends Controller
{

    public function create($month, $year, $day_id)
    {
        $day = Day::findOrFail($day_id);

        if ($day->calendar->user_id !== Auth::id() || $day->calendar->month != $month || $day->calendar->year != $year){
            abort(403, 'Unauthorized action.');
        }
        $users = User::where('id', '!=', Auth::id())->get();

        return view('zoom_meetings.create', [
            'day' => $day,
            'month' => $month,
            'year' => $year,
        ]);
    }

    public function store(Request $request, $month, $year, $day_id)
    { 
        
        $day = Day::findOrFail($day_id);

        if ($day->calendar->user_id !== Auth::id() || $day->calendar->month != $month || $day->calendar->year != $year){
            abort(403, 'Unauthorized action.');
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
            'title_zoom' => $request->title_zoom,
            'topic_zoom' => $request->topic_zoom,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
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

    public function edit(ZoomMeeting $zoomMeeting)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ZoomMeeting $zoomMeeting)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ZoomMeeting $zoomMeeting)
    {
        //
    }
}
