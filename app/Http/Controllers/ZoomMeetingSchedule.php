<?php

namespace App\Http\Controllers;

use App\Models\ZoomMeeting;
use Illuminate\Http\Request;

class ZoomMeetingSchedule extends Controller
{
    
    public function index($month, $year, $day_id)
    {
        return view('zoomMeeting.index');
    }

   
    public function create()
    {
        //
    }

    
    public function store(Request $request)
    {
        //
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
