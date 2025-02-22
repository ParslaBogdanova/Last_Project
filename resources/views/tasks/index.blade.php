<x-app-layout>

    <head>
        <link rel="stylesheet" href="{{ asset('css/tasks.css') }}">
        <script src="{{ asset('js/tasks.js') }}" defer></script>
    </head>
    <main class="main-content">
        <div class="card-container">
            <div class="info-card">
                <h1>More exchange messages</h1>
                <p>Like discord and other things</p>
            </div>

            <div class="unread-messages">
                <h1>Unread messages</h1>
                <p>This will be after only I create a friends list.</p>
            </div>

            <div class="task-info">
                <h1>Task List</h1>
                <div class="task-list" id="task-list">
                    @foreach ($tasks as $task)
                        <div class="task-item" data-task-id="{{ $task->id }}">

                            <input type="checkbox" class="rounded checkbox"
                                onchange="checkingTasks(this, {{ $task->id }})"
                                @if ($task->completed) checked @endif>

                            <a href="{{ route('tasks.show', $task->id) }}"
                                class="{{ $task->completed ? 'completed' : '' }}">
                                {{ $task->description }}
                            </a>
                            <form action="{{ route('tasks.destroy', $task->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="delete-btn">X</button>
                            </form>

                        </div>
                    @endforeach
                </div>

                <form id="create-task-form" class="task-form hidden" action="{{ route('tasks.store') }}" method="POST">
                    @csrf
                    <label for="description">Description:</label>
                    <input type="text" id="description" name="description" placeholder="Description" required>
                    <button type="submit">Save Task</button>
                </form>

                <a id="create-task-button" class="create-task">
                    Create New Task
                </a>
            </div>
        </div>

        <div class="schedule-container">
            @foreach ($weekDays as $day)
                <div class="calendar-day {{ $day['date']->isToday() ? 'today' : '' }}">
                    <span class="day-name">{{ $day['name'] }}</span>
                    <span class="day-number">{{ $day['formattedDate'] }}</span>
                </div>
            @endforeach
        </div>

    </main>
</x-app-layout>
