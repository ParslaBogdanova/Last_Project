<?php

namespace App\Http\Controllers;

use App\Models\ZoomCall;
use App\Models\ZoomMeeting;
use Carbon\Carbon;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class ZoomCallController extends Controller {

    public function index() {
        $currentDate = Carbon::now('Europe/Riga')->toDateString();
        $currentTime = Carbon::now('Europe/Riga')->toTimeString();
    
        $userId = Auth::id();
    
        $zoomMeeting = ZoomMeeting::where('date', $currentDate)
            ->where(function ($query) use ($userId) {
                $query->where('creator_id', $userId)
                    ->orWhereHas('invitedUsers', function ($subQuery) use ($userId) {
                        $subQuery->where('users.id', $userId);
                    });
            })
            ->where(function ($query) use ($currentTime) {
                $query->where('start_time', '<=', $currentTime)
                    ->where('end_time', '>=', $currentTime)
                    ->orWhere('start_time', '>', $currentTime);
            })
            ->orderBy('start_time')
            ->first();
    
        if (!$zoomMeeting) {
            return view('zoom-meeting.index', [
                'zoomMeeting' => null,
                'zoomCalls' => null,
                'message' => 'No Zoom meetings for you scheduled for today.'
            ]);
        }
    
        $now = Carbon::now('Europe/Riga');
        $startTime = Carbon::parse($zoomMeeting->start_time, 'Europe/Riga');
        $endTime = Carbon::parse($zoomMeeting->end_time, 'Europe/Riga');
    
        if ($now->lessThan($startTime)) {
            return view('zoom-meeting.index', [
                'zoomMeeting' => $zoomMeeting,
                'zoomCalls' => collect(),
                'message' => 'Meeting scheduled for later today, please check back later.'
            ]);
        }
    
        if ($now->between($startTime, $endTime)) {
            $zoomCalls = ZoomCall::with('user')
                ->where('zoom_meetings_id', $zoomMeeting->id)
                ->where('status', 'active')
                ->get();
    
            return view('zoom-meeting.index', [
                'zoomMeeting' => $zoomMeeting,
                'zoomCalls' => $zoomCalls,
                'message' => null
            ]);
        }
    
        return view('zoom-meeting.index', [
            'zoomMeeting' => $zoomMeeting,
            'zoomCalls' => collect(),
            'message' => 'This meeting has already ended.'
        ]);
    }
    

   


    public function store(Request $request) {
        $user = Auth::user();
        $now = Carbon::now('Europe/Riga');
        $zoomMeetingId = $request->zoom_meetings_id;
        $today = now()->toDateString();
        $nowTime = now()->format('H:i:s');
    
        $zoomMeeting = ZoomMeeting::findOrFail($zoomMeetingId);
    
        if ($zoomMeeting->date !== $today) {
            return response()->json(['message' => 'This meeting is not scheduled for today.'], 403);
        }
    
        if ($nowTime < $zoomMeeting->start_time || ($zoomMeeting->end_time && $nowTime > $zoomMeeting->end_time)) {
            return response()->json(['message' => 'This meeting is not currently active.'], 403);
        }

        $isInvited = DB::table('user_zoom_meetings')
            ->where('zoom_meetings_id', $zoomMeetingId)
            ->where('user_id', $user->id)
            ->where('date', $today)
            ->exists();
    
        if (!$isInvited) {
            return response()->json(['message' => 'You are not invited to this meeting today.'], 403);
        }
    
        $zoomCall = ZoomCall::updateOrCreate(
            ['zoom_meetings_id' => $zoomMeetingId, 'user_id' => $user->id],
            ['status' => 'active']
        );
    
        return response()->json(['message' => 'Call joined', 'call' => $zoomCall]);
    }




    public function show($zoom_meetings_id, $title_zoom) {
    $zoomMeeting = ZoomMeeting::where('id', $zoom_meetings_id)
        ->where('title_zoom', $title_zoom)
        ->firstOrFail();

    $zoomCalls = ZoomCall::with('user')
        ->where('zoom_meetings_id', $zoomMeeting->id)
        ->where('status', 'active')
        ->get();

    $now = Carbon::now('Europe/Riga');

    if ($now->greaterThanOrEqualTo($zoomMeeting->start_time)) {
        return view('zoom-meeting.index', [
            'zoomMeeting' => $zoomMeeting,
            'zoomCalls'=> $zoomCalls,
        ]);
    }

    return response()->json(['message' => 'The meeting has not started yet.'], 403);
}




    public function update(Request $request, ZoomCall $zoomCall) {
        $user = Auth::user();

        if ($zoomCall->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $zoomCall->endCall();

        return response()->json(['message' => 'Call ended', 'call' => $zoomCall]);
    }




    public function destroy(ZoomCall $zoomCall) {
        $zoomCall->delete();
        return response()->json(['message' => 'The zoom meeting has been canceled']);
    }




    public function start($zoomMeetingId) {
        $zoomMeeting = ZoomMeeting::findOrFail($zoomMeetingId);
        $zoomMeeting->update(['status' => 'active']);
        $zoomMeeting->zoomCalls()->update(['status' => 'active']);
    
        return redirect()->route('zoom.show', [
            'zoom_meetings_id' => $zoomMeeting->id,
            'title_zoom' => $zoomMeeting->title_zoom,
        ]);
        
    }
    



    public function end($zoomMeetingId) {
        $zoomMeeting = ZoomMeeting::findOrFail($zoomMeetingId);
        $zoomMeeting->update(['status' => 'ended']);
        $zoomMeeting->zoomCalls()->update(['status' => 'ended']);
    
        return response()->json(['message' => 'Call ended.']);
    }



    public function joinScreen($zoomMeetingId) {
        $zoomMeeting = ZoomMeeting::findOrFail($zoomMeetingId);
        
        if ($zoomMeeting->status !== 'active') {
            return response()->json(['message' => 'Meeting is not active.'], 403);
        }
        
        return view('zoom-meeting.join', [
            'zoomMeeting'=> $zoomMeeting,
        ]);
    }  
}
