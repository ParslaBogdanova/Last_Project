<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Notification;
use App\Models\ReminderZoomMeeting;
use App\Models\Day;
use App\Models\Task;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TaskController extends Controller
{
    public function index()
    {
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();
        $userId = Auth::id(); 

        $notifications = Notification::where('user_id', Auth::id())->latest()->get();
        $reminders = ReminderZoomMeeting::where('user_id', $userId)->with('zoomMeeting')->get();
    
        $weekDays = [];
        for ($day = $weekStart; $day->lte($weekEnd); $day->addDay()) {
            $weekDays[] = [
                'name' => $day->format('l'),
                'date' => $day->copy(),
                'formattedDate' => $day->format('M d')
            ];
        }
    
        $tasks = Task::where('user_id', Auth::id())->get();
        return view('tasks.index', [
            'tasks' => $tasks,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'weekDays' => $weekDays,
            'notifications' => $notifications,
            'reminders' => $reminders,
        ]);
    }
    
    
    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:255',
        ]);

        Task::create([
            'description' => $request->description,
            'user_id' => Auth::id(),
            'completed' => false,
        ]);

        return redirect()->route('tasks.index');
    }

    public function destroy($id)
    {
        $task = Task::where('user_id', Auth::id())->findOrFail($id);
        $task->delete();

        return redirect()->route('tasks.index');
    }

    public function updateCompleted(Request $request, $id)
    {
        $task = Task::where('user_id', Auth::id())->findOrFail($id);
        $task->completed = $request->completed;
        $task->save();

        return response()->json(['message' => 'Task updated successfully.']);
    }

    public function resetWeeklyData()
    {
        $weekEnd = Carbon::now()->endOfWeek();
    }
}
