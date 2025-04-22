<x-app-layout>

    <head>
        <link rel="stylesheet" href="{{ asset('css/calendar_show.css') }}">
        <script src="{{ asset('js/calendar_show.js') }}" defer></script>
    </head>

    <div class="container">
        <div class="form-container">
            <h2>Add Schedule</h2>
            <form method="POST"
                action="{{ route('schedules.store', ['month' => $month, 'year' => $year, 'date' => $day->date]) }}">
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
                @if ($day->blockedDays->where('user_id', Auth::id())->isNotEmpty())
                    <div class="blocked-day">
                        <strong>This day is blocked.</strong><br>
                        Reason: {{ $day->blockedDays->where('user_id', Auth::id())->first()->reason }}
                    </div>
                @endif
            @endif


            <div class="calendar-container">
                <div class="calendar-day-details">
                    @if ($day->blockedDays->where('user_id', Auth::id())->isNotEmpty())
                        <div class="blocked-reason">
                            <strong>Reason for Blocking:</strong>
                            {{ $day->blockedDays->where('user_id', Auth::id())->first()->reason }}
                        </div>

                        <form
                            action="{{ route('calendar.unblock', ['month' => $month, 'year' => $year, 'date' => $day->date]) }}"
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
                                action="{{ route('calendar.blockDay', ['month' => $month, 'year' => $year, 'date' => $day->date]) }}"
                                method="POST" id="block-form">
                                @csrf
                                <textarea name="reason" id="reason" rows="4" placeholder="Enter reason for blocking"></textarea>
                                <button type="submit" class="submit-btn">Submit</button>
                            </form>
                        </div>
                        <div id="zoomForm" style="display:none;">
                            <form
                                action="{{ route('zoom_meetings.store', ['month' => $month, 'year' => $year, 'date' => $day->date]) }}"
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
                                    @if (session('unavailable_users'))
                                        <div id="error-message" style="color: red;">
                                            <p>The following users are unavailable:</p>
                                            <ul>
                                                @foreach (session('unavailable_users') as $user)
                                                    <li>{{ $user['name'] }} - {{ $user['reason'] }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
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
                @if ($schedule->user_id === Auth::id())
                    <div class="schedule-item" data-id="{{ $schedule->id }}"
                        style="background-color: {{ $schedule->color }};"
                        onclick="openScheduleDetails({{ $schedule->id }})">
                        <div class="delete-schedule" onclick="deleteSchedule(event, {{ $schedule->id }})">X</div>
                        <div class="schedule-title">{{ $schedule->title }}</div>
                        <div class="schedule-description">{{ $schedule->description }}</div>
                    </div>
                @endif
            @endforeach


            @foreach ($zoomMeetings as $zoomMeeting)
                @php
                    $isCreator = $zoomMeeting->creator_id === auth()->id();
                    $isInvited = $zoomMeeting->invitedUsers->pluck('id')->contains(auth()->id());
                    $bgColor = $isCreator ? '#99d0d1' : ($isInvited ? 'orange' : 'lightgrey');
                @endphp

                @if ($isCreator || $isInvited)
                    <div class="zoomMeeting-item" data-id="{{ $zoomMeeting->id }}"
                        style="background-color: {{ $bgColor }};"
                        @if ($isCreator) onclick="openZoomMeeting({{ $zoomMeeting->id }})" @endif>

                        @if ($isCreator)
                            <div class="delete-zoomMeeting" onclick="deleteZoomMeeting(event, {{ $zoomMeeting->id }})">
                                X
                            </div>
                        @endif

                        <div class="zoomMeeting-title_zoom"><strong>Title:</strong> {{ $zoomMeeting->title_zoom }}
                        </div>
                        <div class="zoomMeeting-topic_zoom"><strong>Topic:</strong> {{ $zoomMeeting->topic_zoom }}
                        </div>
                        <div class="zoomMeeting-start_time"><strong>Start Time:</strong> {{ $zoomMeeting->start_time }}
                        </div>
                        <div class="zoomMeeting-end_time"><strong>End Time:</strong> {{ $zoomMeeting->end_time }}</div>

                        <div class="zoomMeeting-invited_users">
                            @if ($isCreator)
                                <strong>Invited Users:</strong>
                                @if ($zoomMeeting->invitedUsers->isNotEmpty())
                                    @foreach ($zoomMeeting->invitedUsers as $invitedUser)
                                        <span>{{ $invitedUser->name }}</span>{{ !$loop->last ? ', ' : '' }}
                                    @endforeach
                                @else
                                    <span>No users invited</span>
                                @endif
                            @else
                                <strong>Creator:</strong> {{ $zoomMeeting->creator->name }}
                            @endif
                        </div>
                    </div>
                @endif
            @endforeach
            @if ($day->schedules->count() === 0)
                <p>No schedules for this day.</p>
            @endif
        </div>

        <!-- Edit form -->
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

        <div class="zoomMeeting-details-container" id="zoomMeetingDetailsContainer">
            <button class="close-btn" onclick="closeZoomMeetingDetails()">X</button>
            <h2>Edit ZoomMeeting</h2>
            <form method="POST" id="editZoomMeeting">
                @csrf
                @method('PUT')
                <input type="hidden" name="zoom_meetings_id" id="zoomMeetingIdInput">
                <div>
                    <label for="edit_title_zoom">Title</label>
                    <input type="text" id="edit_title_zoom" name="title_zoom" required>
                </div>
                <div>
                    <label for="edit_topic_zoom">Topic</label>
                    <textarea id="edit_topic_zoom" name="topic_zoom"></textarea>
                </div>
                <div>
                    <label for="edit_start_time">Start Time</label>
                    <input type="time" id="edit_start_time" name="start_time" required>
                </div>
                <div>
                    <label for="edit_end_time">End Time</label>
                    <input type="time" id="edit_end_time" name="end_time">
                </div>
                <div>
                    <label for="edit_invited_users">Invited Users</label>
                    <select name="invited_users[]" id="edit_invited_users" multiple required>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}"
                                {{ in_array($user->id, old('invited_users', [])) ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                    @if (session('unavailable_users'))
                        <div id="error-message" style="color: red;">
                            <p>The following users are unavailable:</p>
                            <ul>
                                @foreach (session('unavailable_users') as $user)
                                    <li>{{ $user['name'] }} - {{ $user['reason'] }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div id="invitedUsersList"></div>
                </div>
                <button type="submit">Update Meeting</button>
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
                "{{ route('schedules.update', ['month' => $month, 'year' => $year, 'date' => $day->date, 'id' => '']) }}/" +
                selectedSchedule.id;
        }



        let invitedUsers = [];

        function openZoomMeeting(zoomMeetingId) {
            const zoomMeetings = @json($day->zoomMeetings);
            const selectedZoomMeeting = zoomMeetings.find(s => s.id === zoomMeetingId);

            if (!selectedZoomMeeting) {
                console.error("Zoom meeting not found:", zoomMeetingId);
                return;
            }

            console.log("Selected Zoom Meeting Data:", selectedZoomMeeting);

            document.getElementById('zoomMeetingIdInput').value = selectedZoomMeeting.id;
            document.getElementById('edit_title_zoom').value = selectedZoomMeeting.title_zoom;
            document.getElementById('edit_topic_zoom').value = selectedZoomMeeting.topic_zoom;
            document.getElementById('edit_start_time').value = selectedZoomMeeting.start_time;
            document.getElementById('edit_end_time').value = selectedZoomMeeting.end_time;

            if (invitedUsers.length === 0) {
                invitedUsers = selectedZoomMeeting.invited_users || selectedZoomMeeting.users.map(user => user.id) || [];
            }

            updateInvitedUsersUI();

            document.getElementById('editZoomMeeting').action =
                "{{ route('zoom_meetings.update', ['month' => $month, 'year' => $year, 'date' => $day->date, 'id' => '']) }}/" +
                selectedZoomMeeting.id;

            document.getElementById('zoomMeetingDetailsContainer').classList.add('visible');
        }

        function updateInvitedUsersUI() {
            const allUsers = @json($users);
            const invitedUsersList = document.getElementById('invitedUsersList');
            const invitedUsersSelect = document.getElementById('edit_invited_users');

            invitedUsersList.innerHTML = '';
            invitedUsersSelect.innerHTML = '';

            const invitedUsersSet = new Set(invitedUsers);
            allUsers.forEach(user => {
                if (!invitedUsersSet.has(user.id)) {
                    let option = document.createElement('option');
                    option.value = user.id;
                    option.textContent = user.name;
                    invitedUsersSelect.appendChild(option);
                }
            });
        }
    </script>
</x-app-layout>
