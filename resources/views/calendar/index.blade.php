<x-app-layout>
    <style>
        .calendar-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 2rem;
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            max-width: 800px;
            margin-bottom: 1rem;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 0.5rem;
            width: 100%;
            max-width: 800px;
        }

        .day-box {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 1rem;
            text-align: center;
            height: 100px;
            cursor: pointer;
            position: relative;
        }

        .day-box:hover {
            background-color: #f0f0f0;
        }

        .today {
            background-color: #ffefc2;
        }

        .add-event-btn {
            margin-top: 1rem;
            padding: 0.5rem 1rem;
            background-color: #4caf50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .add-event-btn:hover {
            background-color: #45a049;
        }

        #eventModal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            width: 400px;
        }

        #eventModal h3 {
            margin-top: 0;
        }

        #eventModal input,
        #eventModal textarea,
        #eventModal button {
            width: 100%;
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        #eventModal button {
            background-color: #4caf50;
            color: white;
            border: none;
            cursor: pointer;
        }

        #eventModal button:hover {
            background-color: #45a049;
        }

        #modalOverlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
        }

        .selected-date {
            font-size: 16px;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .event-bubble {
            position: absolute;
            bottom: 5px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #4caf50;
            color: white;
            padding: 5px;
            border-radius: 3px;
            font-size: 0.75rem;
            width: 80%;
            text-align: center;
        }
    </style>

    <div class="calendar-container">
        <div class="calendar-header">
            <button onclick="changeMonth(-1)">&#8592; Previous</button>
            <h2 id="month-year"></h2>
            <button onclick="changeMonth(1)">Next &#8594;</button>
        </div>

        <div class="calendar-grid" id="calendar-days">
        </div>
    </div>

    <div id="modalOverlay" onclick="closeSchedule()"></div>
    <div id="eventModal">
        <div id="selectedDateTitle" class="selected-date"></div>
        <h3>Add Schedule</h3>
        <form id="eventForm" method="POST" action="{{ route('calendar.store') }}">
            @csrf
            <input type="hidden" name="day_id" id="day_id">
            <label for="title">Title:</label>
            <input type="text" name="title" id="eventTitle" required>
            <label for="description">Description:</label>
            <textarea name="description" id="eventDescription" rows="4" required></textarea>
            <label for="color">Color:</label>
            <input type="color" name="color" id="eventColor" value="#4caf50">
            <button type="submit">Save Event</button>
        </form>
        <button onclick="closeSchedule()">Cancel</button>
    </div>

    <script>
        const today = new Date();
        let currentMonth = today.getMonth();
        let currentYear = today.getFullYear();

        document.addEventListener('DOMContentLoaded', () => {
            renderCalendar();
        });

        function renderCalendar() {
            const daysContainer = document.getElementById('calendar-days');
            const monthYear = document.getElementById('month-year');

            // Clear previous days
            daysContainer.innerHTML = '';

            // Update header
            const months = [
                'January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'
            ];
            monthYear.innerText = `${months[currentMonth]} ${currentYear}`;

            // Get first day of the month and total days in the month
            const firstDay = new Date(currentYear, currentMonth, 1).getDay();
            const totalDays = new Date(currentYear, currentMonth + 1, 0).getDate();

            // Render blank spaces for days before the first day of the month
            for (let i = 0; i < firstDay; i++) {
                const blankDay = document.createElement('div');
                blankDay.classList.add('day-box');
                daysContainer.appendChild(blankDay);
            }

            // Render days of the month
            for (let day = 1; day <= totalDays; day++) {
                const dayBox = document.createElement('div');
                dayBox.classList.add('day-box');
                dayBox.innerText = day;

                // Highlight today
                if (
                    day === today.getDate() &&
                    currentMonth === today.getMonth() &&
                    currentYear === today.getFullYear()
                ) {
                    dayBox.classList.add('today');
                }

                // Check if there's an event for this day
                @foreach ($schedules as $schedule)
                    if ({{ $schedule->day_id }} === day) {
                        const eventBubble = document.createElement('div');
                        eventBubble.classList.add('event-bubble');
                        eventBubble.innerText = '{{ $schedule->title }}';
                        dayBox.appendChild(eventBubble);
                    }
                @endforeach

                dayBox.addEventListener('click', () => openAddSchedule(day));
                daysContainer.appendChild(dayBox);
            }
        }

        function changeMonth(direction) {
            currentMonth += direction;

            if (currentMonth < 0) {
                currentMonth = 11;
                currentYear--;
            } else if (currentMonth > 11) {
                currentMonth = 0;
                currentYear++;
            }

            renderCalendar();
        }

        function openAddSchedule(day) {
            document.getElementById('day_id').value = day;

            const months = [
                'January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'
            ];
            const selectedDateTitle = `${day}, ${months[currentMonth]} ${currentYear}`;
            document.getElementById('selectedDateTitle').innerText = selectedDateTitle;

            document.getElementById('modalOverlay').style.display = 'block';
            document.getElementById('eventModal').style.display = 'block';
        }

        function closeEventModal() {
            document.getElementById('modalOverlay').style.display = 'none';
            document.getElementById('eventModal').style.display = 'none';
        }
    </script>
</x-app-layout>
