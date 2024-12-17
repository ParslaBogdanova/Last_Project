<?php

namespace App\Http\Controllers;

use App\Models\Calendar;
use App\Models\Day;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CalendarController extends Controller
{
    public function index($month = null, $year = null)
    {
        $currentDate = \Carbon\Carbon::now();
        $month = $month ?? Carbon::now()->month;
        $year = $year ?? Carbon::now()->year;
    
        $calendar = Calendar::where('year', $year)->where('month', $month)
        ->where('user_id', Auth::id())->first();
        
        if (!$calendar) {
            $calendar = Calendar::create([
                'year' => $year,
                'month' => $month,
                'user_id' => Auth::id(),
            ]);
            $this->generateDaysForMonth($calendar);
        }
    
        $prevMonth = $month == 1 ? 12 : $month - 1;
        $prevYear = $month == 1 ? $year - 1 : $year;
        $nextMonth = $month == 12 ? 1 : $month + 1;
        $nextYear = $month == 12 ? $year + 1 : $year;
    
        $days = $calendar->days;

        $schedules = Schedule::whereIn('day_id', $days->pluck('id'))
        ->where('user_id', Auth::id())->get();
    
        return view('calendar.index', [
            'calendar' => $calendar,
            'days' => $days,
            'schedules' => $schedules,
            'prevMonth' => $prevMonth,
            'prevYear' => $prevYear,
            'nextMonth' => $nextMonth,
            'nextYear' => $nextYear,
            'year' => $year,
            'month' => $month,
        ]);
    }

    public function createSchedule(Request $request, $month, $year)
{
    // Now, we're using the 'day_id' from the form
    $dayId = $request->input('day_id');

    $day = Day::findOrFail($dayId);

    $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'color' => 'required|string|max:7',
    ]);

    Schedule::create([
        'title' => $request->input('title'),
        'description' => $request->input('description'),
        'color' => $request->input('color'),
        'user_id' => Auth::id(),
        'day_id' => $day->id,
    ]);

    return redirect()->route('calendar.index', ['month' => $month, 'year' => $year]);
}
    private function generateDaysForMonth(Calendar $calendar)
    {
        $firstDayOfMonth = Carbon::create($calendar->year, $calendar->month, 1);
        $daysInMonth = $firstDayOfMonth->daysInMonth;
    
        foreach (range(1, $daysInMonth) as $day) {
            // Check if the day already exists for this calendar
            if (!Day::where('calendar_id', $calendar->id)->where('date', $firstDayOfMonth->copy()->day($day)->toDateString())->exists()) {
                Day::create([
                    'calendar_id' => $calendar->id,
                    'date' => $firstDayOfMonth->copy()->day($day)->toDateString(),
                ]);
            }
        }
    }
    

    public function changeMonth(Request $request, $direction)
    {
        $year = $request->year ?? Carbon::now()->year;
        $month = $request->month ?? Carbon::now()->month;

        $newDate = Carbon::create($year, $month, 1)->addMonth($direction);

        return redirect()->route('calendar.index', [
            'year' => $newDate->year,
            'month' => $newDate->month,
        ]);
    }
}