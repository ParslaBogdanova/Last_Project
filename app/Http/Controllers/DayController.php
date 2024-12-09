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
    
        // Generate days for the calendar
        $startOfMonth = \Carbon\Carbon::create($year, $month, 1);
        $daysInMonth = $startOfMonth->daysInMonth;
    
        $days = [];
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $date = $startOfMonth->copy()->day($i)->toDateString();
    
            // Fetch schedules for the day using the day_id
            $dayId = Day::where('date', $date)->value('id');
            $schedules = Schedule::where('day_id', $dayId)->get();
    
            $days[] = [
                'date' => $date,
                'id' => $dayId,
                'schedules' => $schedules,
            ];
        }
    
        return view('calendar.index', compact('days', 'month', 'year'));
    }
    // Show the schedule for a specific day
    public function show($dayId)
    {
        // Find the day by ID and fetch its schedules
        $day = Day::findOrFail($dayId);
        $schedules = Schedule::where('day_id', $dayId)->get();

        return view('calendar.day.index', ['day' => $day, 'schedules' => $schedules]);
    }

    // Show the form to create a new schedule for a specific day
    public function create($dayId)
    {
        return view('calendar.day.create', ['day_id' => $dayId]);
    }

    // Store the new schedule for a day
    public function store(Request $request, $dayId)
{
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'color' => 'nullable|string|max:7',
    ]);

    $day = Day::findOrFail($dayId);

    // Create a new schedule for the selected day
    $schedule = new Schedule();
    $schedule->day_id = $dayId;  // This associates the schedule with the correct day
    $schedule->title = $validated['title'];
    $schedule->description = $validated['description'];
    $schedule->color = $validated['color'];
    $schedule->save();

    // Redirect back to the day view with a success message
    return redirect()->route('calendar.day', ['dayId' => $dayId])
                     ->with('success', 'Schedule added successfully!');
}

}
