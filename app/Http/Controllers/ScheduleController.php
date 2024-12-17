<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{

    public function index()
    {
        // Fetch all schedules to display in the calendar
        $schedules = Schedule::where('user_id', Auth::id())->get();
        return view('calendar.index', ['schedules' => $schedules]);
    }

    public function show($id)
    {
        $schedule = Schedule::where('user_id', Auth::id())->findOrFail($id);
        return view('calendar.show', ['schedule' => $schedule]);
    }

    public function store(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'title' => 'required|string|',
            'description' => 'nullable',
            'color' => 'required|string|max:7',
        ]);

        // Create the schedule in the database
        Schedule::create([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'color' => $request->input('color'),
            'user_id' => Auth::id(),
        ]);

        // Redirect back to the calendar index view
        return redirect()->route('calendar.index');
    }
}
