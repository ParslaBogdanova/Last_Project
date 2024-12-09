<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $schedules = Schedule::whereMonth('created_at', $today->month)
                             ->whereYear('created_at', $today->year)
                             ->get();

        $groupedSchedules = $schedules->groupBy('day_id');
        $days = $this->getDaysOfMonth($today);

        return view('calendar.index', [
            'groupedSchedules' => $groupedSchedules,
            'days' => $days,
            'today' => $today
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'day_id' => 'required|date',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'required|string'
        ]);

        // Check if the schedule already exists for this day
        $existingSchedule = Schedule::where('day_id', $validated['day_id'])
            ->where('title', $validated['title'])
            ->first();

        if ($existingSchedule) {
            return response()->json([
                'success' => false,
                'message' => 'Schedule already exists for this day.'
            ], 400); 
        }

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
        $schedule = Schedule::findOrFail($id);
        $schedule->delete();

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
