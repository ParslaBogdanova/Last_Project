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
        $month = $month ?? Carbon::now()->month;
        $year = $year ?? Carbon::now()->year;

        $daysInMonth = Carbon::create($year, $month)->daysInMonth;
        $days = [];

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $dayDate = Carbon::create($year, $month, $i)->toDateString();
            $schedules = Schedule::whereDate('date', $dayDate)->get();

            $days[] = [
                'date' => $dayDate,
                'schedules' => $schedules
            ];
        }

        $today = Carbon::today()->toDateString();

        return view('calendar.index', [
            'days' => $days,
            'month' => $month,
            'year' => $year,
            'today' => $today
        ]);
    }

    public function getDaySchedules($dayId)
    {
        $schedules = Schedule::whereDate('date', $dayId)->get();

        return response()->json(['schedules' => $schedules]);
    }
}
