<x-app-layout>
    <style>
        .calendar-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 2rem;
            font-family: Arial, sans-serif;
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            max-width: 800px;
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 0.5rem;
            width: 100%;
            max-width: 800px;
            margin-bottom: 2rem;
        }

        .day-box {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 1rem;
            text-align: center;
            height: 100px;
            cursor: pointer;
            position: relative;
            background-color: white;
        }

        .day-box:hover {
            background-color: #f0f0f0;
        }

        .today {
            background-color: #ffefc2;
            font-weight: bold;
            border: 2px solid #ffbf47;
        }

        .modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 700px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 1rem;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            flex-direction: column;
        }

        .modal-header {
            font-size: 1.25rem;
            margin-bottom: 1rem;
        }

        .modal-close {
            cursor: pointer;
            font-size: 1.2rem;
            float: right;
        }

        .modal-backdrop {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .schedule-list {
            width: 100%;
            padding-right: 20px;
            border-right: 2px solid #ddd;
            height: 400px;
            overflow-y: auto;
        }

        .create-schedule-form {
            width: 100%;
            padding-left: 20px;
            height: 400px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            display: none;
        }

        .create-schedule-form label {
            font-weight: bold;
        }

        .create-schedule-form input,
        .create-schedule-form textarea {
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ddd;
            font-size: 1rem;
        }

        .create-schedule-form button {
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.1rem;
        }

        .create-schedule-form button:hover {
            background-color: #0056b3;
        }

        .add-schedule-btn-container {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }

        .add-schedule-btn {
            font-size: 1.5rem;
            color: #007bff;
            cursor: pointer;
            background-color: transparent;
            border: none;
            margin-top: 10px;
        }

        .schedule-box-container {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .schedule-box-container>div {
            margin-bottom: 10px;
        }
    </style>

    <div class="calendar-container">
        <div class="calendar-header">
            <button onclick="changeMonth(-1)">&#8592; Previous</button>
            <h2 id="month-year">{{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}</h2>
            <button onclick="changeMonth(1)">Next &#8594;</button>
        </div>

        <div class="calendar-grid" id="calendar-days">
            @foreach ($days as $day)
                <div class="day-box @if ($day['date'] == \Carbon\Carbon::today()->toDateString()) today @endif"
                    onclick="openModal('{{ $day['date'] }}')">
                    <span>{{ \Carbon\Carbon::parse($day['date'])->day }}</span>

                    <div id="schedules-for-{{ $day['date'] }}" class="schedule-summary">
                        @foreach ($day['schedules'] as $schedule)
                            <div class="schedule-item" style="background-color: {{ $schedule->color }};">
                                {{ $schedule->title }}
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="modal-backdrop" id="modal-backdrop"></div>
    <div class="modal" id="day-modal">
        <div class="modal-header">
            <span id="modal-date"></span>
            <span class="modal-close" onclick="closeModal()">×</span>
        </div>
        <div class="modal-body schedule-box-container">
            <div class="schedule-list" id="schedule-list-container">
                <p>Loading schedules...</p>
            </div>

            <div class="create-schedule-form" id="create-schedule-form-container">
                <form id="create-schedule-form">
                    <input type="hidden" id="schedule-day-id" name="day_id" value="">
                    <div>
                        <label for="schedule-title">Title</label>
                        <input type="text" id="schedule-title" name="title" required>
                    </div>
                    <div>
                        <label for="schedule-description">Description</label>
                        <textarea id="schedule-description" name="description"></textarea>
                    </div>
                    <div>
                        <label for="schedule-color">Color</label>
                        <input type="color" id="schedule-color" name="color" value="#ffffff">
                    </div>
                    <button type="submit">Create Schedule</button>
                </form>
            </div>

            <div class="add-schedule-btn-container" id="add-schedule-btn-container">
                <button class="add-schedule-btn" id="add-schedule-btn" onclick="openCreateScheduleModal()">+</button>
            </div>
        </div>
    </div>

    <script>
        const modal = document.getElementById('day-modal');
        const modalBackdrop = document.getElementById('modal-backdrop');
        const modalDate = document.getElementById('modal-date');
        const scheduleListContainer = document.getElementById('schedule-list-container');
        const createScheduleFormContainer = document.getElementById('create-schedule-form-container');

        function openModal(date) {
            modalDate.innerText = `Date: ${date}`;
            modal.style.display = 'flex';
            modalBackdrop.style.display = 'block';
            document.getElementById('schedule-day-id').value = date;
            fetchSchedules(date);
        }

        function closeModal() {
            modal.style.display = 'none';
            modalBackdrop.style.display = 'none';
        }

        function fetchSchedules(date) {
            fetch(`/calendar/day/${date}/schedules`)
                .then(response => response.json())
                .then(data => {
                    const schedules = data.schedules;
                    if (schedules.length > 0) {
                        let scheduleHtml = '';
                        schedules.forEach(schedule => {
                            scheduleHtml += `
                        <div class="schedule-item" style="background-color: ${schedule.color};">
                            ${schedule.title} - ${schedule.description}
                        </div>`;
                        });
                        scheduleListContainer.innerHTML = scheduleHtml;
                    } else {
                        scheduleListContainer.innerHTML = `<p>No schedules available for this day.</p>`;
                    }
                });
        }

        function openCreateScheduleModal() {
            createScheduleFormContainer.style.display = 'block';
            document.getElementById('add-schedule-btn-container').style.display = 'none';
        }

        document.getElementById('create-schedule-form').addEventListener('submit', function(event) {
            event.preventDefault();
            const dayId = document.getElementById('schedule-day-id').value;
            const title = document.getElementById('schedule-title').value;
            const description = document.getElementById('schedule-description').value;
            const color = document.getElementById('schedule-color').value;

            const formData = new FormData();
            formData.append('day_id', dayId);
            formData.append('title', title);
            formData.append('description', description);
            formData.append('color', color);

            fetch(`/calendar/day/${dayId}/schedule/store`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const newSchedule = data.schedule;
                        const newScheduleHtml = `
                <div class="schedule-item" style="background-color: ${newSchedule.color};">
                    ${newSchedule.title} - ${newSchedule.description}
                </div>`;
                        scheduleListContainer.innerHTML += newScheduleHtml;
                        createScheduleFormContainer.style.display = 'none';
                        document.getElementById('add-schedule-btn-container').style.display = 'block';
                    }
                })
                .catch(error => console.log(error));
        });

        function changeMonth(direction) {
            let currentMonth = {{ $month }};
            let currentYear = {{ $year }};
            currentMonth += direction;

            if (currentMonth < 1) {
                currentMonth = 12;
                currentYear--;
            } else if (currentMonth > 12) {
                currentMonth = 1;
                currentYear++;
            }

            window.location.href = `/calendar/${currentMonth}/${currentYear}`;
        }
    </script>
</x-app-layout>
