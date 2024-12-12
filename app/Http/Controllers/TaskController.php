<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = Task::where('user_id', Auth::id())->get();
        return view('tasks.index', ['tasks' => $tasks]);
    }

    public function show($id)
{
    $task = Task::where('user_id', Auth::id())->findOrFail($id);
    return view('tasks.show', ['task' => $task]);
}

public function edit($id)
{
    $task = Task::where('user_id', Auth::id())->findOrFail($id);
    return view('tasks.edit', ['task' => $task]);
}


    public function store(Request $request)
    {

        $request->validate([
            'description' => 'required|string',
        ]);

        Task::create([
            'description' => $request->input('description'),
            'user_id' => Auth::id(),
        ]);
        return redirect()->route('tasks.index');
    }

    public function destroy($id)
    {
        $task = Task::where('user_id', Auth::id())->findOrFail($id);
        $task->delete();
        return redirect()->route('tasks.index');
    }
    public function updateCompleted(Request $request, Task $task)
{
    $validated = $request->validate([
        'completed' => 'required|boolean',
    ]);

    $task->completed = $validated['completed'];
    $task->save();

    return response()->json(['success' => true]);
}
}