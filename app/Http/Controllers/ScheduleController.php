<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today();

        // Fetch schedules for the logged-in user for the current month/year
        $schedules = Schedule::where('user_id', $user->id)
            ->whereMonth('created_at', $today->month)
            ->whereYear('created_at', $today->year)
            ->get();

        // Group schedules by day for easier display
        $groupedSchedules = $schedules->groupBy('day_id');

        return view('calendar.index', [
            'groupedSchedules' => $groupedSchedules,
            'today' => $today,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'day_id' => 'required|date',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'required|string',
        ]);

        // Check if the schedule already exists for the day and user
        $existingSchedule = Schedule::where('user_id', $user->id)
            ->where('day_id', $validated['day_id'])
            ->where('title', $validated['title'])
            ->first();

        if ($existingSchedule) {
            return response()->json([
                'success' => false,
                'message' => 'Schedule already exists for this day.',
            ], 400);
        }

        $schedule = Schedule::create([
            'user_id' => $user->id, // Associate schedule with the authenticated user
            'day_id' => $validated['day_id'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'color' => $validated['color'],
        ]);

        return response()->json([
            'success' => true,
            'schedule' => $schedule,
        ]);
    }

    public function destroy($id)
    {
        $user = Auth::user();

        // Find the schedule belonging to the user and delete it
        $schedule = Schedule::where('user_id', $user->id)->findOrFail($id);
        $schedule->delete();

        return response()->json([
            'success' => true,
            'message' => 'Schedule deleted successfully!',
        ]);
    }

    public function getSchedulesForDay($date)
    {
        $user = Auth::user();

        // Fetch schedules for the authenticated user on a specific date
        $schedules = Schedule::where('user_id', $user->id)
            ->where('day_id', $date)
            ->get();

        return response()->json(['schedules' => $schedules]);
    }
}
