<?php

namespace App\Http\Controllers;

use App\Models\Day;
use App\Models\Schedule;
use Illuminate\Http\Request;

class DayController extends Controller
{
    public function index($month = null, $year = null)
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;
    
        $startOfMonth = \Carbon\Carbon::create($year, $month, 1);
        $daysInMonth = $startOfMonth->daysInMonth;
    
        $days = [];
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $date = $startOfMonth->copy()->day($i)->toDateString();
            $dayId = Day::where('date', $date)->value('id');
            $schedules = Schedule::where('day_id', $dayId)->get();
    
            $days[] = [
                'date' => $date,
                'id' => $dayId,
                'schedules' => $schedules,
            ];
        }
    
        return view('calendar.index', [
            'days' => $days,
            'month' => $month,
            'year' => $year
        ]);
    }

    public function show($dayId)
    {
        $day = Day::findOrFail($dayId);
        $schedules = Schedule::where('day_id', $dayId)->get();

        return view('calendar.day.index', [
            'day' => $day,
            'schedules' => $schedules
        ]);
    }

    public function create($dayId)
    {
        return view('calendar.day.create', [
            'day_id' => $dayId
        ]);
    }

    public function store(Request $request, $dayId)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
        ]);

        $day = Day::findOrFail($dayId);

        $schedule = new Schedule();
        $schedule->day_id = $dayId;
        $schedule->title = $validated['title'];
        $schedule->description = $validated['description'];
        $schedule->color = $validated['color'];
        $schedule->save();

        return redirect()->route('calendar.day', ['dayId' => $dayId])
                         ->with('success', 'Schedule added successfully!');
    }
}
