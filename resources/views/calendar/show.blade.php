<x-app-layout>

    <head>
        <link rel="stylesheet" href="{{ asset('css/calendar_show.css') }}">
        <script src="{{ asset('js/calendar_show.js') }}" defer></script>
    </head>

    <div class="container">
        <div class="form-container">
            <h2>Add Schedule</h2>
            <form method="POST"
                action="{{ route('schedules.store', ['month' => $month, 'year' => $year, 'day_id' => $day->id]) }}">
                @csrf
                <div>
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" required>
                </div>
                <div>
                    <label for="description">Description</label>
                    <textarea id="description" name="description"></textarea>
                </div>
                <div>
                    <label for="color">Color</label>
                    <input type="color" id="color" name="color" required>
                </div>
                <button type="submit">Save Schedule</button>
                <button type="button" class="back-to-calendar-btn"
                    onclick="window.location.href='{{ route('calendar.index', ['month' => $month, 'year' => $year]) }}'">Back</button>
            </form>

            @if ($day->isBlocked)
                <div class="blocked-day">
                    <strong>This day is blocked.</strong><br>
                    Reason: {{ $day->blockedDays->reason }}
                </div>
            @endif

            <div class="calendar-container">
                <div class="calendar-day-details">
                    @if ($day->blockedDays()->exists())
                        <div class="blocked-reason">
                            <strong>Reason for Blocking:</strong> {{ $day->blockedDays()->first()->reason }}
                        </div>

                        <form
                            action="{{ route('calendar.unblock', ['month' => $month, 'year' => $year, 'day_id' => $day->id]) }}"
                            method="POST" id="unblock-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="unblock-button">Unblock This Day</button>
                        </form>
                    @else
                        <div class="dropdown-container">
                            <button class="dropdown-toggle" id="blockDropdownBtn">+</button>
                            <div class="dropdown-options" id="dropdownOptions" style="display: none;">
                                <a href="javascript:void(0)" id="blockDayBtn">Block Day</a>
                                <a href="javascript:void(0)" id="createZoomMeeting">Create Zoom Meeting</a>
                            </div>
                        </div>

                        <div id="blockForm" style="display: none;">
                            <form
                                action="{{ route('calendar.blockDay', ['month' => $month, 'year' => $year, 'day_id' => $day->id]) }}"
                                method="POST" id="block-form">
                                @csrf
                                <textarea name="reason" id="reason" rows="4" placeholder="Enter reason for blocking"></textarea>
                                <button type="submit" class="submit-btn">Submit</button>
                            </form>
                        </div>
                        <div id="zoomForm" style="display:none;">
                            <form
                                action="{{ route('zoom_meetings.store', ['month' => $month, 'year' => $year, 'day_id' => $day->id]) }}"
                                method="POST" id="zoom-form">
                                @csrf
                                <div>
                                    <label for="title_zoom">Title</label>
                                    <input type="text" id="title_zoom" name="title_zoom" required>
                                </div>
                                <div>
                                    <label for="topic_zoom">Topic</label>
                                    <textarea id="topic_zoom" name="topic_zoom"></textarea>
                                </div>
                                <div>
                                    <label for="start_time">Start Time</label>
                                    <input type="time" id="start_time" name="start_time" required>
                                </div>
                                <div>
                                    <label for="end_time">End Time</label>
                                    <input type="time" id="end_time" name="end_time">
                                </div>
                                <div>
                                    <label for="invited_users">Invited Users</label>
                                    <select name="invited_users[]" id="invited_users" multiple required>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}"
                                                {{ in_array($user->id, old('invited_users', [])) ? 'selected' : '' }}>
                                                {{ $user->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit">Create Zoom Meeting</button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Existing schedules container -->
        <div class="schedule-list-container">
            <h2>Existing Schedules</h2>
            @foreach ($day->schedules as $schedule)
                <div class="schedule-item" data-id="{{ $schedule->id }}"
                    style="background-color: {{ $schedule->color }};"
                    onclick="openScheduleDetails({{ $schedule->id }})">
                    <div class="delete-schedule" onclick="deleteSchedule(event, {{ $schedule->id }})">X</div>
                    <div class="schedule-title">{{ $schedule->title }}</div>
                    <div class="schedule-description">{{ $schedule->description }}</div>
                </div>
            @endforeach

            @if ($day->schedules->count() === 0)
                <p>No schedules for this day.</p>
            @endif
        </div>

        <!-- Edit schedule form -->
        <div class="schedule-details-container" id="scheduleDetailsContainer">
            <button class="close-btn" onclick="closeScheduleDetails()">X</button>
            <h2>Edit Schedule</h2>
            <form method="POST" id="editScheduleForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="schedule_id" id="scheduleIdInput">
                <div>
                    <label for="edit_title">Title</label>
                    <input type="text" id="edit_title" name="title" required>
                </div>
                <div>
                    <label for="edit_description">Description</label>
                    <textarea id="edit_description" name="description"></textarea>
                </div>
                <div>
                    <label for="edit_color">Color</label>
                    <input type="color" id="edit_color" name="color" required>
                </div>
                <button type="submit">Save Changes</button>
            </form>
        </div>
    </div>

    <script>
        function openScheduleDetails(scheduleId) {
            const schedule = @json($day->schedules);
            const selectedSchedule = schedule.find(s => s.id === scheduleId);

            document.getElementById('scheduleIdInput').value = selectedSchedule.id;
            document.getElementById('edit_title').value = selectedSchedule.title;
            document.getElementById('edit_description').value = selectedSchedule.description;
            document.getElementById('edit_color').value = selectedSchedule.color;

            const detailsContainer = document.getElementById('scheduleDetailsContainer');
            detailsContainer.classList.add('visible');

            document.getElementById('editScheduleForm').action =
                "{{ route('schedules.update', ['month' => $month, 'year' => $year, 'day_id' => $day->id, 'id' => '']) }}/" +
                selectedSchedule.id;
        }
    </script>
</x-app-layout>
