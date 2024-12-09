<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index()
{
    // Get the current date
    $today = Carbon::today();
    
    // Get all schedules for the current month
    $schedules = Schedule::whereMonth('created_at', $today->month)
                         ->whereYear('created_at', $today->year)
                         ->get();
    
    // Group schedules by 'day_id' (assuming you have 'day_id' to represent the day for a schedule)
    $groupedSchedules = $schedules->groupBy('day_id');
    
    // Get the days of the month (assumed already available)
    $days = $this->getDaysOfMonth($today);
    
    // Pass the grouped schedules and days to the view
    return view('calendar.index', compact('groupedSchedules', 'days', 'today'));
}

    public function store(Request $request)
    {
        // Validate and store the schedule
        $validated = $request->validate([
            'day_id' => 'required|date',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'required|string'
        ]);
    
        // Check if the schedule already exists for this day (if needed, add a check to avoid duplicate entries)
        $existingSchedule = Schedule::where('day_id', $validated['day_id'])
            ->where('title', $validated['title'])  // Optional: You could also check title/description for duplicates
            ->first();
    
        if ($existingSchedule) {
            return response()->json([
                'success' => false,
                'message' => 'Schedule already exists for this day.'
            ], 400);  // Return error if duplicate exists
        }
    
        // Create and save the new schedule
        $schedule = Schedule::create([
            'day_id' => $validated['day_id'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'color' => $validated['color']
        ]);
    
        return response()->json([
            'success' => true,
            'schedule' => $schedule
        ]);
    }

    public function destroy($id)
    {
        // Directly find the schedule by ID and delete
        $schedule = Schedule::findOrFail($id);
        $schedule->delete();

        // Return success message after deletion
        return response()->json([
            'success' => true,
            'message' => 'Schedule deleted successfully!'
        ]);
    }
    public function getSchedulesForDay($date)
{
    $schedules = Schedule::where('day_id', $date)->get();
    return response()->json(['schedules' => $schedules]);
}
}
