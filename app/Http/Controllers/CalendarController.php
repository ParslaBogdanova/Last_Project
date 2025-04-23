<?php

namespace App\Http\Controllers;

use App\Models\Calendar;
use App\Models\Day;
use App\Models\User;
use App\Models\ZoomMeeting;
use App\Models\BlockedDays;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarController extends Controller {
    public function index($month = null, $year = null) {
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
    
        $user = Auth::user();
        $zoomMeetings = ZoomMeeting::all();
    
        $this->generateDaysForMonth($calendar);
    
        return view('calendar.index', [
            'calendar' => $calendar,
            'days' => $calendar->days()->get(),
            'prevMonth' => $prevMonth,
            'prevYear' => $prevYear,
            'nextMonth' => $nextMonth,
            'nextYear' => $nextYear,
            'year' => $year,
            'month' => $month,
            'zoomMeetings' => $zoomMeetings, 
        ]);
    }
    


    public function show($month, $year, $date) {
        $day = Day::with([
            'schedules',
            'blockedDays',
            'zoomMeetings',
            'zoomMeetings.invitedUsers'
        ])->whereHas('calendar', function ($query) {
            $query->where('user_id', Auth::id());
        })->where('date', $date)->firstOrFail();
    
        $userId = Auth::id();
        $zoomMeetings = ZoomMeeting::with(['invitedUsers', 'creator'])
            ->where('date', $day->date) 
            ->where(function ($query) use ($userId) {
                $query->where('creator_id', $userId)
                      ->orWhereHas('invitedUsers', function ($subQuery) use ($userId) {
                          $subQuery->where('user_id', $userId);
                      });
            })
            ->get();
    
        $users = User::where('id', '!=', Auth::id())->get();
    
        return view('calendar.show', [
            'day' => $day,
            'schedules' => $day->schedules,
            'blockedDays' => $day->blockedDays,
            'zoomMeetings' => $zoomMeetings,
            'month' => $month,
            'year' => $year,
            'users' => $users,
        ]);
    }
    

    private function generateDaysForMonth(Calendar $calendar) {
        $firstDayOfMonth = Carbon::create($calendar->year, $calendar->month, 1);
        $daysInMonth = $firstDayOfMonth->daysInMonth;

        foreach (range(1, $daysInMonth) as $day) {
            Day::firstOrCreate([
                'calendar_id' => $calendar->id,
                'date' => $firstDayOfMonth->copy()->day($day)->toDateString(),
            ]);
        }
    }



    public function changeMonth($direction) {
        $currentDate = Carbon::now();
        $month = $currentDate->month;
        $year = $currentDate->year;

        $newDate = Carbon::create($year, $month, 1)->addMonth($direction);

        return redirect()->route('calendar.index', [
            'year' => $newDate->year,
            'month' => $newDate->month,
        ]);
    }



    public function blockDay(Request $request, $month, $year, $date) {
        $userId = Auth::id();
    
        $day = Day::where('date', $date)
                  ->whereHas('calendar', function ($query) use ($userId) {
                      $query->where('user_id', $userId);
                  })
                  ->firstOrFail();
    
        $blockedDay = BlockedDays::updateOrCreate(
            ['date' => $date, 'user_id' => $userId], 
            [
                'reason' => $request->input('reason'),
                'status' => true,
                'calendar_id' => $day->calendar_id,
            ]
        );
    
        return redirect()->route('calendar.show', [
            'month' => $month,
            'year' => $year,
            'date' => $day->date
            ])->with('status', 'Day blocked!');
    }


    
    public function unblock(Request $request, $month, $year, $date) {
        $userId = Auth::id();

        $blockedDay = BlockedDays::where('date', $date)
            ->where('user_id', $userId)
            ->first();
    
            if ($blockedDay) {
                $blockedDay->delete();
                return redirect()->back();
            }
        }
}
