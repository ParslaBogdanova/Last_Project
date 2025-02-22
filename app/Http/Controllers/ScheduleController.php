<?php

namespace App\Http\Controllers;

use App\Models\Day;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    public function create($month, $year, $day_id)
    {
        $day = Day::findOrFail($day_id);

        if ($day->calendar->user_id !== Auth::id() || $day->calendar->month != $month || $day->calendar->year != $year) {
            abort(403, 'Unauthorized action.');
        }

        return view('schedules.create', [
            'day' => $day,
            'month' => $month,
            'year' => $year,
        ]);
    }

    public function store(Request $request, $month, $year, $day_id)
    {
        $day = Day::findOrFail($day_id);

        if ($day->calendar->user_id !== Auth::id() || $day->calendar->month != $month || $day->calendar->year != $year) {
            abort(403, 'Unauthorized action.');
        }

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

        return redirect()->route('calendar.show', [
            'month' => $month,
            'year' => $year,
            'day_id' => $day_id,
        ]);
    }

    public function edit($month, $year, $day_id, $id)
    {
        $schedule = Schedule::findOrFail($id);

        if ($schedule->user_id !== Auth::id() || $schedule->day_id != $day_id) {
            abort(403, 'Unauthorized action.');
        }

        return view('schedules.edit', [
            'schedule' => $schedule,
            'month' => $month,
            'year' => $year,
            'day_id' => $day_id,
        ]);
    }

    public function update(Request $request, $month, $year, $day_id)
{
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

    return redirect()->route('calendar.show', ['month' => $month, 'year' => $year, 'day_id' => $day_id]);
}


public function destroy($month, $year, $day_id, $schedule_id)
{
    $schedule = Schedule::find($schedule_id);

    if ($schedule) {
        $schedule->delete();
        return response()->json(['message' => 'Schedule deleted successfully.'], 200);
    }

    return response()->json(['message' => 'Schedule not found.'], 404);
}

public function blockDay($month, $year, $day_id)
{
    $day = Day::findOrFail($day_id);
    $day->is_blocked = true;
    $day->save();

    return redirect()->route('calendar.show', ['month' => $month, 'year' => $year, 'day_id' => $day_id])
                     ->with('status', 'Day blocked!');
}

public function cancelBlockDay($month, $year, $day_id)
{
    $day = Day::findOrFail($day_id);

    $day->is_blocked = false;
    $day->save();

    return redirect()->route('calendar.show', ['month' => $month, 'year' => $year, 'day_id' => $day_id])
                     ->with('status', 'Day block canceled!');
}
}
