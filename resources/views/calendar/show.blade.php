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

            @foreach ($day->zoomMeetings as $zoomMeeting)
                <div class="zoomMeeting-item" data-id="{{ $zoomMeeting->id }}" style="background-color:darkslategrey"
                    onclick="openZoomMeeting({{ $zoomMeeting->id }})">
                    <div class="delete-zoomMeeting" onclick="deleteZoomMeeting(event, {{ $zoomMeeting->id }})">X</div>
                    <div class="zoomMeeting-title_zoom">{{ $zoomMeeting->title_zoom }}</div>
                    <div class="zoomMeeting-topic_zoom">{{ $zoomMeeting->topic_zoom }}</div>
                    <div class="zoomMeeting-start_time">{{ $zoomMeeting->start_time }}</div>
                    <div class="zoomMeeting-end_time">{{ $zoomMeeting->end_time }}</div>
                    <div class="zoomMeeting-invited_users">
                        @if ($zoomMeeting->users->isNotEmpty())
                            @foreach ($zoomMeeting->users as $invitedUser)
                                <span>{{ $invitedUser->name }}</span>
                                @if (!$loop->last)
                                    ,
                                @endif
                            @endforeach
                        @else
                            <span>No users invited</span>
                        @endif
                    </div>
                </div>
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
                "{{ route('schedules.update', ['month' => $month, 'year' => $year, 'day_id' => $day->id, 'id' => '']) }}/" +
                selectedSchedule.id;
        }

        let invitedUsers = []; // Store invited users globally

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

            // Load invited users only on first open
            if (invitedUsers.length === 0) {
                invitedUsers = selectedZoomMeeting.invited_users || selectedZoomMeeting.users.map(user => user.id) || [];
            }

            updateInvitedUsersUI();

            // Fix: Dynamically update the form action URL
            document.getElementById('editZoomMeeting').action =
                "{{ route('zoom_meetings.update', ['month' => $month, 'year' => $year, 'day_id' => $day->id, 'id' => '']) }}/" +
                selectedZoomMeeting.id;

            // Make the form visible
            document.getElementById('zoomMeetingDetailsContainer').classList.add('visible');
        }

        // Update the UI without refreshing the entire form
        function updateInvitedUsersUI() {
            const allUsers = @json($users);
            const invitedUsersList = document.getElementById('invitedUsersList');
            const invitedUsersSelect = document.getElementById('edit_invited_users');

            invitedUsersList.innerHTML = '';
            invitedUsersSelect.innerHTML = '';

            // Show already invited users with remove ("-") button
            invitedUsers.forEach(userId => {
                let user = allUsers.find(u => u.id == userId);
                if (user) {
                    let userItem = document.createElement('div');
                    userItem.innerHTML = `<span>${user.name} </span>
                <button type="button" class="remove-user" data-user-id="${user.id}">-</button>`;
                    invitedUsersList.appendChild(userItem);
                }
            });

            // Attach event listener to remove buttons
            document.querySelectorAll('.remove-user').forEach(button => {
                button.addEventListener('click', function() {
                    let userId = this.getAttribute('data-user-id');
                    invitedUsers = invitedUsers.filter(id => id != userId);
                    updateInvitedUsersUI(); // Update only the invited users list
                });
            });

            // Show only uninvited users in dropdown
            const invitedUsersSet = new Set(invitedUsers);
            allUsers.forEach(user => {
                if (!invitedUsersSet.has(user.id)) {
                    let option = document.createElement('option');
                    option.value = user.id;
                    option.textContent = user.name;
                    invitedUsersSelect.appendChild(option);
                }
            });

            // Handle adding a new invited user
            invitedUsersSelect.addEventListener('change', function() {
                let newUserId = this.value;
                if (newUserId && !invitedUsers.includes(newUserId)) {
                    invitedUsers.push(newUserId);
                    updateInvitedUsersUI(); // Only update the user list
                }
            });
        }
    </script>
</x-app-layout>
