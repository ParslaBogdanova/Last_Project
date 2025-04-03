<x-app-layout>

    <head>
        <link rel="stylesheet" href="{{ asset('css/tasks.css') }}">
        <script src="{{ asset('js/tasks.js') }}" defer></script>
    </head>
    <main class="main-content">
        <div class="card-container">
            <div class="info-card">
                <h1>Notification</h1>
                @if ($notifications->count() > 0)
                    <div class="notifications">
                        <ul>
                            @foreach ($notifications as $notification)
                                <li>
                                    {{ $notification->message }}
                                    @if ($notification->zoomMeeting)
                                        (Created by: {{ $notification->zoomMeeting->creator->name }})
                                    @endif
                                    ({{ $notification->created_at->diffForHumans() }})
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <p>No notifications yet.</p>
                @endif

            </div>
            <div class="unread-messages">
                @foreach ($reminders as $reminder)
                    @php
                        $currentTime = \Carbon\Carbon::now('Europe/Riga');

                        $startTime = \Carbon\Carbon::createFromFormat(
                            'H:i',
                            $reminder->zoomMeeting->start_time,
                            'Europe/Riga',
                        );

                        $startTime->setDate($currentTime->year, $currentTime->month, $currentTime->day);
                        $timeUntilMeeting = $currentTime->diff($startTime);
                        $hoursLeft = $timeUntilMeeting->h;
                        $minutesLeft = $timeUntilMeeting->i;
                        $secondsLeft = $timeUntilMeeting->s;
                    @endphp

                    @if ($currentTime->lt($startTime))
                        <li>
                            <strong>Reminder:</strong> Your Zoom meeting
                            <b>{{ $reminder->zoomMeeting->title_zoom }}</b> starts at
                            <b>{{ $startTime->format('H:i') }}</b>!

                            (Created by: {{ $reminder->zoomMeeting->creator->name }})
                            <div>
                                Countdown: {{ $hoursLeft }} hours, {{ $minutesLeft }} minutes, and
                                {{ $secondsLeft }} seconds left.
                            </div>
                        </li>
                    @endif
                @endforeach
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

                <form id="create-task-form" class="task-form hidden" action="{{ route('tasks.store') }}"
                    method="POST">
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
