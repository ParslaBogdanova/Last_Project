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
            /* Ensure 7 columns */
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
    </style>

    <div class="calendar-container">
        <div class="calendar-header">
            <button onclick="changeMonth(-1)">&#8592; Previous</button>
            <h2 id="month-year"></h2>
            <button onclick="changeMonth(1)">Next &#8594;</button>
        </div>

        <!-- Corrected Calendar Grid -->
        <div class="calendar-grid" id="calendar-days">
            <!-- Days will be dynamically inserted here -->
        </div>

        <button class="add-event-btn" onclick="openAddEventModal()">+ Add Event</button>
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
                'January', 'February', 'March', 'April', 'May',
                'June', 'July', 'August', 'September', 'October',
                'November', 'December'
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

                // Add event click handler
                dayBox.addEventListener('click', () => openDayDetails(day));
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

        function openAddEventModal() {
            alert('Feature to add an event will open here!');
        }

        function openDayDetails(day) {
            alert(`Details for ${day}/${currentMonth + 1}/${currentYear}`);
        }
    </script>
</x-app-layout>
