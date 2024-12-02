<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index()
    {
        $schedules = Schedule::all();
        return view('calendar.index', ['schedules' => $schedules]);
    }

    public function store(Request $request)
    {
        \Log::info($request->all());

        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'color' => 'nullable|string',
        ]);

        Schedule::create([
            'day_id' => $request->day_id,
            'title' => $request->title,
            'description' => $request->description,
            'color' => $request->color,
        ]);

        return redirect()->route('calendar.index');
    }

    public function show(Schedule $schedule)
    {
        return view('calendar.show', ['schedule' => $schedule]);
    }

    public function update(Request $request, Schedule $schedule)
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'color' => 'nullable|string',
        ]);

        $schedule->update([
            'title' => $request->title,
            'description' => $request->description,
            'color' => $request->color,
        ]);

        return redirect()->route('calendar.index');
    }

    public function destroy(Schedule $schedule)
    {
        $schedule->delete();
        return redirect()->route('calendar.index');
    }
}

