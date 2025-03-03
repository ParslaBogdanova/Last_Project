<?php

namespace App\Http\Controllers;

use App\Models\Calendar;
use App\Models\Day;
use App\Models\ZoomMeeting;
use App\Models\User;
use App\Models\BlockedDays;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index($month = null, $year = null)
    {
        $currentDate = Carbon::now();
        $month = $month ?? $currentDate->month;
        $year = $year ?? $currentDate->year;

        $calendar = Calendar::firstOrCreate(
            [
                'year' => $year,
                'month' => $month,
                'user_id' => Auth::id(),
            ]
        );

        $this->generateDaysForMonth($calendar);

        $prevMonth = $month == 1 ? 12 : $month - 1;
        $prevYear = $month == 1 ? $year - 1 : $year;
        $nextMonth = $month == 12 ? 1 : $month + 1;
        $nextYear = $month == 12 ? $year + 1 : $year;

        return view('calendar.index', [
            'calendar' => $calendar,
            'days' => $calendar->days,
            'prevMonth' => $prevMonth,
            'prevYear' => $prevYear,
            'nextMonth' => $nextMonth,
            'nextYear' => $nextYear,
            'year' => $year,
            'month' => $month,
        ]);
    }



    public function show($month, $year, $day_id)
    {
        $day = Day::with(['schedules', 'blockedDays', 'zoomMeetings'])->findOrFail($day_id);

        if ($day->calendar->user_id !== Auth::id() || $day->calendar->month != $month || $day->calendar->year != $year) {
            abort(403, 'Unauthorized action.');
        }
        $users = User::where('id', '!=', Auth::id())->get();

        return view('calendar.show', [
            'day' => $day,
            'schedules' => $day->schedules,
            'blockedDays' => $day->blockedDays,
            'zoomMeetings' => $day->zoomMeetings,
            'month' => $month,
            'year' => $year,
            'users' => $users,
        ]);
    }



    private function generateDaysForMonth(Calendar $calendar)
    {
        $firstDayOfMonth = Carbon::create($calendar->year, $calendar->month, 1);
        $daysInMonth = $firstDayOfMonth->daysInMonth;

        foreach (range(1, $daysInMonth) as $day) {
            Day::firstOrCreate([
                'calendar_id' => $calendar->id,
                'date' => $firstDayOfMonth->copy()->day($day)->toDateString(),
            ]);
        }
    }



    public function changeMonth($direction)
    {
        $currentDate = Carbon::now();
        $month = $currentDate->month;
        $year = $currentDate->year;

        $newDate = Carbon::create($year, $month, 1)->addMonth($direction);

        return redirect()->route('calendar.index', [
            'year' => $newDate->year,
            'month' => $newDate->month,
        ]);
    }



    public function blockDay(Request $request, $month, $year, $day_id) {

    $userId = Auth::id();
        if (!$userId) {
            return redirect()->back()->with('error', 'User is not authenticated.');
        }

    $day = Day::findOrFail($day_id);

    $blockedDay = BlockedDays::updateOrCreate(
        ['day_id' => $day_id, 'user_id' => $userId], 
        [
            'reason' => $request->input('reason'),
            'status' => true,
            'calendar_id' => $day->calendar_id,
        ]
    );

    return redirect()->back()->with('success', 'Day blocked successfully.');
}

    

    public function unblock(Request $request, $month, $year, $day_id) {
        $blockedDay = BlockedDays::where('day_id', $day_id)->first();
    
        if ($blockedDay) {
            $blockedDay->delete();
    
            return redirect()->back()->with('success', 'Day unblocked successfully.');
        }
    
        return redirect()->back()->with('error', 'This day is not blocked.');
    }
}
