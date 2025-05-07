<?php

namespace App\Http\Controllers;

use App\Models\Day;
use App\Models\User;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller {


/**
 * Store a new schedule in the database.
 *
 * This method handles the creation of a new schedule for a specific date.
 * It validates the incoming request and saves the schedule to the database.
 *
 * @param \Illuminate\Http\Request $request The incoming request containing the schedule data.
 * @param int $month The month for which the schedule is being created.
 * @param int $year The year for which the schedule is being created.
 * @param string $date The date for which the schedule is being created.
 * 
 * @return \Illuminate\Http\RedirectResponse Redirects to the calendar view after saving the schedule.
 * (shows in calendar.show and calendar.index view)
 */
    public function store(Request $request, $month, $year, $date) {
        $day = Day::where('date', $date)
          ->whereHas('calendar', function ($query) {
              $query->where('user_id', Auth::id());
          })
          ->firstOrFail();
    
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'required|string|max:7',
        ]);
    
        Schedule::create([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'color' => $request->input('color'),
            'date' => $date,
            'user_id' => Auth::id(),
        ]);
    
        return redirect()->route('calendar.show', [
            'month' => $month,
            'year' => $year,
            'date' => $date,
        ]);
    }
    

/**
 * Update an existing schedule.
 *
 * This method updates the details of an existing schedule. It validates the incoming data
 * and updates the schedule record in the database.
 *
 * @param \Illuminate\Http\Request $request The incoming request containing the updated schedule data.
 * @param int $month The month of the schedule being updated.
 * @param int $year The year of the schedule being updated.
 * @param string $date The date of the schedule being updated.
 * 
 * @return \Illuminate\Http\RedirectResponse Redirects back to the calendar view(both calendar folder views) after updating the schedule.
 */
    public function update(Request $request, $month, $year, $date) {

        $validatedData = $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'required|string',
        ]);
    
        $schedule = Schedule::findOrFail($validatedData['schedule_id']);
        $schedule->update([
            'title' => $validatedData['title'],
            'description' => $validatedData['description'],
            'color' => $validatedData['color'],
    
        ]);

        return redirect()->route('calendar.show', [
            'month' => $month,
            'year' => $year,
            'date' => $date
        ]);
    }


/**
 * Delete a specific schedule.
 *
 * This method deletes a schedule record from the database. It takes the schedule ID
 * and removes the associated schedule entry.
 *
 * @param int $month The month in which the schedule exists.
 * @param int $year The year in which the schedule exists.
 * @param string $date The date on which the schedule exists.
 * @param int $schedule_id The ID of the schedule to be deleted.
 * 
 * @return \Illuminate\Http\RedirectResponse Redirects back to the calendar view(calendar.show) after deletion.
 */
    public function destroy($month, $year, $date, $schedule_id) {
        $schedule = Schedule::find($schedule_id);
        $schedule->delete();
    }
}
