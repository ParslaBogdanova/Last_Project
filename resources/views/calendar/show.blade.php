<x-app-layout>

    <head>
        <link rel="stylesheet" href="{{ asset('css/calendar_show.css') }}">
        <script src="{{ asset('js/calendar_show.js') }}" defer></script>
    </head>

    <button type="button" class="back-to-calendar-btn"
        onclick="window.location.href='{{ route('calendar.index', ['month' => $month, 'year' => $year]) }}'">&#8249;
        Back</button>

    <div class="date-navigator">
        <button onclick="navigateDay('prev')">&#8249;</button>
        <div class="date-display">{{ \Carbon\Carbon::parse($day->date)->format('F j, Y') }}</div>
        <button onclick="navigateDay('next')">&#8250;</button>
    </div>

    <div class="container">
        <div class="schedules-creation-container">
            <div class="creation-container">
                <h2>Add your own schedule</h2>
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
                </form>
            </div>

            @if ($day->isBlocked)
                @if ($day->blockedDays->where('user_id', Auth::id())->isNotEmpty())
                    >
                    <strong>This day is blocked.</strong><br>
                    Reason: {{ $day->blockedDays->where('user_id', Auth::id())->first()->reason }}
                @endif
            @endif

            <div class="calendar-day-details">
                @if ($day->blockedDays->where('user_id', Auth::id())->isNotEmpty())
                    <div class="blocked-reason">
                        <strong>Reason for Blocking:</strong> <br>
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
                        <button class="dropdown-toggle" id="blockDropdownBtn">&#10010;</button>
                        <div class="dropdown-options" id="dropdownOptions" style="display: none;">
                            <a href="javascript:void(0)" id="blockDayBtn">Block Day</a>
                            <a href="javascript:void(0)" id="createZoomMeeting">Create Zoom Meeting</a>
                        </div>
                    </div>
                    <div id="blockForm" class="blockForm" style="display: none;">
                        <button class="close-btn" onclick="closeBlockedDays()">&#9866;</button>
                        <form
                            action="{{ route('calendar.blockDay', ['month' => $month, 'year' => $year, 'date' => $day->date]) }}"
                            method="POST" id="block-form">
                            @csrf
                            <textarea name="reason" id="reason" rows="4" placeholder="Enter reason for blocking"></textarea>
                            <button type="submit" class="submit-btn">Submit</button>
                        </form>
                    </div>
                    <div id="zoomForm" class="zoomForm" style="display:none;">
                        <button class="close-btn" onclick="closeZoomForm()">&#9866;</button>
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
                                <input type="time" id="start_time" name="start_time" min="00:00" max="23:59"
                                    required>
                            </div>
                            <div>
                                <label for="end_time">End Time</label>
                                <input type="time" id="end_time" name="end_time" min="00:01" max="23:59">
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
        <div class="schedules-container">
            <div class="schedule-list-container">
                <h2>Your own schedule list</h2>
                <div class="schedule-list">
                    @foreach ($day->schedules as $schedule)
                        @if ($schedule->user_id === Auth::id())
                            <div class="schedule-item" data-id="{{ $schedule->id }}"
                                style="background-color: {{ $schedule->color }};"
                                onclick="openScheduleDetails({{ $schedule->id }})">
                                <div class="delete-schedule" onclick="deleteSchedule(event, {{ $schedule->id }})">X
                                </div>
                                <div class="schedule-title">{{ $schedule->title }}</div>
                                <div class="schedule-description">Topic: {{ $schedule->description }}</div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
        {{-- ZOOM MEETING LIST --}}
        <div class="zoomMeetings-container">
            <div class="zoomMeeting-list-container">
                <h2>Zoom meetings list that you created or are invited to</h2>
                <div class="zoomMeeting-list">
                    @foreach ($zoomMeetings as $zoomMeeting)
                        @php
                            $isCreator = $zoomMeeting->creator_id === auth()->id();
                            $isInvited = $zoomMeeting->invitedUsers->pluck('id')->contains(auth()->id());
                            $bgColor = $isCreator ? '#99d0d1' : ($isInvited ? '#ffa500' : '#99d0d1');
                            $textColor = $isCreator ? '#58898a' : ($isInvited ? '#9c6502' : '#58898a');
                        @endphp

                        @if ($isCreator || $isInvited)
                            <div class="zoomMeeting-item" data-id="{{ $zoomMeeting->id }}"
                                style="background-color: {{ $bgColor }}; color: {{ $textColor }};"
                                @if ($isCreator) onclick="openZoomMeeting({{ $zoomMeeting->id }})" @endif>

                                @if ($isCreator)
                                    <div class="delete-zoomMeeting"
                                        onclick="deleteZoomMeeting(event, {{ $zoomMeeting->id }})">
                                        X</div>
                                @endif

                                <div class="zoomMeeting-title_zoom"><strong>Title:</strong>
                                    {{ $zoomMeeting->title_zoom }}
                                </div>
                                <div class="zoomMeeting-topic_zoom"><strong>Topic:</strong>
                                    {{ $zoomMeeting->topic_zoom }}
                                </div>
                                <div class="zoomMeeting-start_time"><strong>Start Time:</strong>
                                    {{ $zoomMeeting->start_time }}
                                </div>
                                <div class="zoomMeeting-end_time"><strong>End Time:</strong>
                                    {{ $zoomMeeting->end_time }}
                                </div>

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
                </div>
            </div>
        </div>

        <div class ="error-container">
            <div class="unable-invited-users">
                <h2>The following users are unavailable:</h2>
                <div class="error-message-list">
                    @if (session('unavailable_users'))
                        <div id="error-message" class="error-message">
                            <ul>
                                @foreach (session('unavailable_users') as $user)
                                    <li><strong>{{ $user['name'] }}</strong> - {{ $user['reason'] }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    </div>
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
                <div id="invitedUsersList"></div>
            </div>
            <button type="submit">Update Meeting</button>
        </form>

    </div>

    <script>
        function navigateDay(direction) {
            const currentDate = new Date("{{ $day->date }}");
            const newDate = new Date(currentDate);

            newDate.setDate(currentDate.getDate() + (direction === 'next' ? 1 : -1));

            const newDateStr = newDate.toISOString().split('T')[0];

            window.location.href =
                `{{ route('calendar.show', ['month' => '__MONTH__', 'year' => '__YEAR__', 'date' => '__DATE__']) }}`
                .replace('__MONTH__', newDate.getMonth() + 1).replace('__YEAR__', newDate.getFullYear()).replace(
                    '__DATE__', newDateStr);
        }

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
