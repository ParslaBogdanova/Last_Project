<?php

namespace App\Http\Controllers;

use App\Models\Day;
use App\Models\User;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    public function create($month, $year, $date) {
        $day = Day::firstOrFail($date);

        return view('schedules.create', [
            'day' => $day,
            'month' => $month,
            'year' => $year,
        ]);
    }



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



    public function destroy($month, $year, $date, $schedule_id) {
        $schedule = Schedule::find($schedule_id);
        $schedule->delete();
    }
}
