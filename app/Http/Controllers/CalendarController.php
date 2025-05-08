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

/**
 * Display the calendar for a specific month and year.
 * And shows current date/month/year
 * 
 * Carbon - php library, that works with dates and time.
 * 
 * This method generates the calendar for the user, showing the days of the month,
 * along with related Zoom meetings and scheduling information.
 * Also the blocked days reason with a different color.
 * 
 * Using in return function 'days' => $calendar->days()->get(),, it returns for calendar.index view
 * related day entries for specific calendar(for each month.)
 * 
 * @param int|null $month The month to display. Defaults to the current month.
 * @param int|null $year The year to display. Defaults to the current year.
 * 
 * @return \Illuminate\View\View
 */
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
    

/**
 * Show the details for a specific day, including schedules, blocked days, and Zoom meetings.
 * 
 * This method retrieves all relevant information for a day, including user-specific data
 * such as schedules and meetings, and passes them to the view.
 * 
 * whereHas() is used to filter models based on the existence of a relationship. Like Day(the child) has a relationship with a Calendar(the parent), of course if the parent calendar belongs to a current user.
 * It firsts checks if the relationship exists('calendar' in day model.) Then applies
 * inner $query(like($query->where('user_id', Auth::id())) to filter only those related models that match.
 * Then returns only parent models that have at least one related model matching the condition.
 * 
 * @param int $month The month of the day to display.
 * @param int $year The year of the day to display.
 * @param string $date The specific date to display in "Y-m-d" format.
 * 
 * @return \Illuminate\View\View
 */
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
    

/**
 * Generate days for a given calendar (month and year).
 * This method creates day records for each day in the selected month.
 * 
 * Using 'range' to generate an array of numbers from 1 to the number of the days in the month.
 * 
 * @param \App\Models\Calendar $calendar The calendar instance to generate days for.
 * 
 * @return void
 */
    private function generateDaysForMonth(Calendar $calendar) {
        $firstDayOfMonth = Carbon::create($calendar->year, $calendar->month, 1);
        $daysInMonth = $firstDayOfMonth->daysInMonth;

        foreach (range(1, $daysInMonth) as $day) {
            Day::firstOrCreate([
                'calendar_id' => $calendar->id,
                'date' => $firstDayOfMonth->copy()->day($day)->toDateString(),
                //method toDateString formats the date to a simple YYYY-MM-DD string without time.
            ]);
        }
    }


/**
 * Change the current month based on the user's direction (next or previous).
 * Redirects the user to the calendar page for the new month.
 * 
 * @param int $direction The direction to move the calendar (1 for next, -1 for previous).
 * 
 * @return \Illuminate\Http\RedirectResponse
 */
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


/**
 * Block a day for a user, preventing any meetings or events from being scheduled.
 * If a day is blocked, all Zoom meetings involving the user on that day are canceled.
 * If they are the creator, the zoom meeting is deleted as 'cancel'.
 * If they are invited, they are removed from those zoom meetings.
 * 
 * Using firstOrFail() is a method, it tries to find the first matching record from the database.
 * If a Day(date attribute) exists, it returns the first match, if it doesn't, then it throws an exception and Laravel shows 404 error page.
 * 
 * @param \Illuminate\Http\Request $request The incoming request containing the block reason.
 * @param int $month The month of the day being blocked.
 * @param int $year The year of the day being blocked.
 * @param \Carbon\Carbon $date The date being blocked.
 * 
 * @return \Illuminate\Http\RedirectResponse
 */
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

         // 1. Meetings where user is invited
        $invitedMeetings = ZoomMeeting::where('date', $date)
            ->whereHas('invitedUsers', function ($query) use ($userId) {
             $query->where('user_id', $userId);
        })->get();

        foreach ($invitedMeetings as $meeting) {
            $meeting->invitedUsers()->detach($userId);
            //detach method, many-to-many relationship, that removes the connection between two models, without deleting either model from the database.
        }

        // 2. Meetings where user is the creator
        $createdMeetings = ZoomMeeting::where('date', $date)
            ->where('creator_id', $userId)->get();

        foreach ($createdMeetings as $meeting) {
            $meeting->delete();
        }
    
        return redirect()->route('calendar.show', [
            'month' => $month,
            'year' => $year,
            'date' => $day->date
            ])->with('status', 'Day blocked!');
    }


/**
 * Unblock a previously blocked day, restoring availability for meetings.
 * But it needs to be blocked by choosing the same date.
 * 
 * @param \Illuminate\Http\Request $request The incoming request.
 * @param int $month The month of the day being unblocked.
 * @param int $year The year of the day being unblocked.
 * @param \Carbon\Carbon $date The date being unblocked.
 * 
 * @return \Illuminate\Http\RedirectResponse
 */   
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
