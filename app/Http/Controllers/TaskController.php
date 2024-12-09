<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = Task::all();
        return view('tasks.index', ['tasks' => $tasks]);
    }

    public function store(Request $request)
    {
\Log::info($request->all());

        $request->validate([
            'description' => 'required|string',
        ]);

        Task::create([
            'description' => $request->description,
        ]);
        return redirect()->route('tasks.index');

    }

    public function show(Task $task)
    {
        return view('tasks.show', ['task' => $task]);
    }

    public function edit($id)
    {
        $task = Task::findOrFile($id);
        return view('tasks.edit', ['task' => $task]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'description' => 'request',
        ]);

        $task = Task::findOrFail($id);
        $task->update([
            'description' => $request->input('description'),
        ]);

        return redirect()->route('tasks.index');
    }

    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $task->delete();
        return redirect()->route('tasks.index');
    }
}