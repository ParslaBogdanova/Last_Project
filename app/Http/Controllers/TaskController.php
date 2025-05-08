<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\ReminderZoomMeeting;
use App\Models\Day;
use App\Models\User;
use App\Models\ZoomMeeting;
use App\Models\BlockedDays;
use App\Models\Task;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TaskController extends Controller {

/**
 * Display the dashboard, including tasks, notifications, reminders, and zoom meetings.
 *
 * This method calculates the current week's start and end dates, fetches the logged-in user's tasks, 
 * notifications, reminders, and zoom meetings, and passes them to the view.
 * 
 * latest() method is a shortcut for ordering by created_at in descending order.
 * Ensure that recent notifications appear first.
 * 
 * with('zoomMeeting') is eager loading the related zoomMeeting data. 
 * It’s a relationship method(between ZoomMeeting and ReminderZoomMeeting models) that tells Laravel to load the related zoomMeeting model at the same time as the reminders.
 * Based of the ID's.
 *
 * @return \Illuminate\View\View The view containing the dashboard data.
 * Yes.. used 'tasks.index' as a dashboard.
 */
    public function index() {
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();
        $userId = Auth::id(); 
        $zoomMeetings = ZoomMeeting::all();

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
            'zoomMeetings' => $zoomMeetings,
        ]);
    }
    
    
/**
 * Store a newly created task in the database.
 *
 * This method validates the incoming request and saves the new task to the database 
 * with the description provided by the user.
 *
 * @param \Illuminate\Http\Request $request The incoming request containing the task description.
 * 
 * @return \Illuminate\Http\RedirectResponse Redirects back to the task index after storing the task.
 */
    public function store(Request $request) {
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


/**
 * Delete a specific task from the database.
 *
 * This method deletes the task with the given ID, ensuring that the task belongs 
 * to the authenticated user.
 *
 * @param int $id The ID of the task to be deleted.
 * 
 * @return \Illuminate\Http\RedirectResponse Redirects back to the task index after deleting the task.
 */
    public function destroy($id) {
        $task = Task::where('user_id', Auth::id())->findOrFail($id);
        $task->delete();

        return redirect()->route('tasks.index');
    }

    
/**
 * Update the completed status of a specific task.
 *
 * This method updates whether a task is marked as completed based on the user's request.
 *
 * @param \Illuminate\Http\Request $request The incoming request containing the completed status.
 * @param int $id The ID of the task to update.
 * 
 * @return void
 */
    public function updateCompleted(Request $request, $id) {
        $task = Task::where('user_id', Auth::id())->findOrFail($id);
        $task->completed = $request->completed;
        $task->save();
    }


/**
 * Reset the weekly data, specifically handling the transition from one week to the next for Zoom meetings.
 * This method is called when Sunday at midnight hits, signaling the end of the current week.
 *
 * @return void
 */    
    public function resetWeeklyData() {
        $weekEnd = Carbon::now()->endOfWeek();
    }
}
