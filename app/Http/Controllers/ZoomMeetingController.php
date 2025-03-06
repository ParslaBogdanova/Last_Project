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

    public function edit($month, $year, $day_id, $id)
    {
        $zoomMeeting = ZoomMeeting::findOrFail($id);

        if($zoomMeeting->user_id !== Auth::id() || $zoomMeeting->day_id != $day_id){
            abort(403, 'Unauthorized action.');
        }

        return view('zoom_meetings.edit', [
            'zoomMeeting' => $zoomMeeting,
            'month' => $month,
            'year' => $year,
            'day_id' => $day_id,
        ]);
    }

    public function update(Request $request, $month, $year, $day_id)
    {
        $validateData = $request->validate([
            'zoom_meetings_id' => 'required|exists:zoom_meetings,id',
            'title_zoom' => 'required|string',
            'topic_zoom' => 'nullable|string',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'invited_users' => 'nullable|array',
            'invited_users.*' => 'exists:users,id',
        ]);

        $zoomMeeting = ZoomMeeting::findOrFail($validateData['zoom_meetings_id']);

        $zoomMeeting->update([
            'title_zoom' => $validateData['title_zoom'],
            'topic_zoom' =>  $validateData['topic_zoom'],
            'start_time' =>  $validateData['start_time'],
            'end_time' =>  $validateData['end_time'],
        ]);
        $zoomMeeting->users()->sync($request->input('invited_users', []));

        return redirect()->route('calendar.show', [
            'month' => $month,
            'year' => $year,
            'day_id' => $day_id
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($month, $year, $day_id, $zoomMeeting_id)
    {
        $zoomMeeting = ZoomMeeting::find($zoomMeeting_id);

        if($zoomMeeting){
            $zoomMeeting->delete();
            return response()->json(['message' => 'Zoom Meeting deleted successfully.'], 200);
        }
        return response()->json(['message' => 'Zoom Meeting not found.'], 404);
    }
}
