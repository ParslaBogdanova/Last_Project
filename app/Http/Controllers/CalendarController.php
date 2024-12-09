<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Schedule;
use Illuminate\Http\Request;
use App\Models\Day;

class CalendarController extends Controller
{
    public function index($month = null, $year = null)
    {
        $daysInMonth = Carbon::create($year, $month)->daysInMonth;
        $days = [];
    
        // Loop through each day of the month
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $dayDate = Carbon::create($year, $month, $i)->toDateString();
            // Fetch schedules for this day
            $schedules = Schedule::whereDate('date', $dayDate)->get();
            
            // Add day data with schedules to the $days array
            $days[] = [
                'date' => $dayDate,
                'schedules' => $schedules
            ];
        }
    
        // Return the view with data
        return view('calendar.index', [
            'days' => $days,
            'month' => $month,
            'year' => $year,
            'today' => $today
        ]);
    }
    public function getDaySchedules($dayId)
{
    // Fetch all schedules for the given day (in 'dayId' format)
    $schedules = Schedule::whereDate('date', $dayId)->get();

    // Return the schedules as a JSON response
    return response()->json(['schedules' => $schedules]);
}
}
