<x-app-layout>
    <style>
        .calendar {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            /* 7 columns for days of the week */
            gap: 1rem;
            margin-top: 2rem;
        }

        .calendar-header {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            /* 7 columns */
            text-align: center;
            font-weight: bold;
        }

        .calendar-day {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            height: 80px;
            cursor: pointer;
            border: 1px solid #ddd;
            border-radius: 8px;
            transition: background-color 0.3s;
            position: relative;
        }

        .calendar-day:hover {
            background-color: #f5f5f5;
        }

        .calendar-day.selected {
            background-color: #8e44ad;
            color: white;
        }

        .schedule-item {
            background-color: #e0e0e0;
            padding: 5px;
            border-radius: 5px;
            margin-top: 5px;
            text-align: center;
        }

        .month-navigation {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .month-navigation button {
            background-color: #8e44ad;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        .month-navigation button:hover {
            background-color: #7e3d8b;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            padding-top: 100px;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            max-width: 500px;
            width: 80%;
            margin: 0 auto;
        }

        .modal-header {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .schedule-summary {
            margin-top: 10px;
        }

        .schedule-item {
            background-color: #f2f2f2;
            padding: 5px;
            margin: 5px 0;
            border-radius: 4px;
        }

        .close-btn {
            background-color: #ff4d4d;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        .close-btn:hover {
            background-color: #e60000;
        }

        .add-schedule-btn {
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 16px;
            line-height: 18px;
            text-align: center;
            cursor: pointer;
        }

        .add-schedule-btn:hover {
            background-color: #218838;
        }

        .day-box {
            position: relative;
        }
    </style>

    <div class="container">
        <div class="month-navigation">
            <form action="{{ route('calendar.index', ['month' => $prevMonth, 'year' => $prevYear]) }}" method="GET">
                <button type="submit">Previous</button>
            </form>

            <h3>{{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}</h3>

            <form action="{{ route('calendar.index', ['month' => $nextMonth, 'year' => $nextYear]) }}" method="GET">
                <button type="submit">Next</button>
            </form>
        </div>

        <div class="calendar-header">
            <div>Sun</div>
            <div>Mon</div>
            <div>Tue</div>
            <div>Wed</div>
            <div>Thu</div>
            <div>Fri</div>
            <div>Sat</div>
        </div>

        <div class="calendar" id="calendar-days">
            @php
                $firstDayOfMonth = \Carbon\Carbon::create($year, $month, 1)->dayOfWeek;
                $daysInMonth = \Carbon\Carbon::create($year, $month, 1)->daysInMonth;
                $totalCells = $firstDayOfMonth + $daysInMonth;
                $totalRows = ceil($totalCells / 7);
            @endphp

            @for ($i = 0; $i < $firstDayOfMonth; $i++)
                <div class="calendar-day"></div>
            @endfor

            <!-- Display the days of the month -->
            @foreach ($days as $day)
                <div class="calendar-day" onclick="openScheduleForm('{{ $day->id }}')">
                    <span>{{ \Carbon\Carbon::parse($day->date)->day }}</span>
                    @foreach ($day->schedules as $schedule)
                        <div class="schedule-item" style="background-color: {{ $schedule->color }};">
                            {{ $schedule->title }}
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>

        <!-- Day Modal -->
        <div id="dayModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <span id="modalDate">Date: </span>
                    <button class="close-btn" onclick="closeModal()">X</button>
                    <button class="add-schedule-btn" onclick="openScheduleForm()">+</button>
                    <div id="scheduleList" class="schedule-summary">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Schedule Modal -->
    <div id="addScheduleModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add Schedule</h3>
                <button class="close-btn" onclick="closeScheduleForm()">X</button>
            </div>
            <form
                action="{{ route('calendar.createSchedule', ['month' => $month, 'year' => $year, 'date' => $day->date]) }}"
                method="POST" id = "addScheduleForm">
                @csrf
                <input type="hidden" id="scheduleDayId" name="day_id">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" required>
                <br>
                <label for="title">Description:</label>
                <input type="text" id="description" name="description" required>
                <br>
                <label for="color">Color:</label>
                <input type="color" id="color" name="color" required>
                <br>
                <button type="submit">Add Schedule</button>
            </form>
        </div>
    </div>
    </div>

    <script>
        // Open the Day Modal
        function openModal(date) {
            const modal = document.getElementById('dayModal');
            const modalDate = document.getElementById('modalDate');
            modalDate.innerText = `Date: ${date}`;
            fetchSchedules(date);
            modal.style.display = 'flex';
        }

        // Close the Day Modal
        function closeModal() {
            const modal = document.getElementById('dayModal');
            modal.style.display = 'none';
        }

        // Open the Add Schedule Modal
        function openScheduleForm(dayId) {
            const addScheduleModal = document.getElementById('addScheduleModal');
            const scheduleDayIdInput = document.getElementById('scheduleDayId');

            scheduleDayIdInput.value = dayId; // Assign selected day to hidden input
            addScheduleModal.style.display = 'flex';
        }

        // Close the Add Schedule Modal
        function closeScheduleForm() {
            const modal = document.getElementById('addScheduleModal');
            modal.style.display = 'none';
        }
    </script>
</x-app-layout>
