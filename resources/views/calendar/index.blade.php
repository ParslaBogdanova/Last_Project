<x-app-layout>

    <head>
        <link rel="stylesheet" href="{{ asset('css/calendar_index.css') }}">
    </head>
    <div class="container">
        <div class="month-year-display" onclick="monthPicker()">
            {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}
        </div>

        <div id="month-picker-popup">
            <div class="monthsPicker">
                <button onclick="changeYear(-1)">&#8249;</button>
                <span id="popup-year">{{ $year }}</span>
                <button onclick="changeYear(1)">&#8250;</button>
            </div>
            <div class="monthDisplay">
                @for ($m = 1; $m <= 12; $m++)
                    <button onclick="goToMonth({{ $m }})">
                        {{ \Carbon\Carbon::create(null, $m, 1)->format('F') }}
                    </button>
                @endfor
            </div>
        </div>



        <div class="month-navigation">
            <form action="{{ route('calendar.index', ['month' => $prevMonth, 'year' => $prevYear]) }}" method="GET">
                <button type="submit">&#8249; Previous</button>
            </form>
            <form action="{{ route('calendar.index', ['month' => $nextMonth, 'year' => $nextYear]) }}" method="GET">
                <button type="submit">Next &#8250;</button>
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

        <div class="calendar">
            @php
                $firstDayOfMonth = \Carbon\Carbon::create($year, $month, 1)->dayOfWeek;
                $today = \Carbon\Carbon::now()->toDateString();
            @endphp

            @for ($i = 0; $i < $firstDayOfMonth; $i++)
                <div class="calendar-day"></div>
            @endfor

            @foreach ($days as $day)
                @php
                    $isToday = $day->date === $today;
                    $isBlocked = $day->blockedDays()->exists();
                @endphp
                <a href="{{ route('calendar.show', ['month' => $month, 'year' => $year, 'date' => $day->date]) }}">
                    <div
                        class="calendar-day {{ $isToday ? 'today' : '' }} {{ $day->blockedDays->where('user_id', Auth::id())->isNotEmpty() ? 'blocked' : '' }}">
                        <span class="day-number">{{ \Carbon\Carbon::parse($day->date)->day }}</span>
                        @php
                            $blocked = $day->blockedDays->where('user_id', Auth::id())->first();
                        @endphp
                        @if ($blocked)
                            <div class ="blocked-day-reason">
                                <strong>Reason:</strong> <br>
                                {{ $blocked->reason }}
                            </div>
                        @endif
                        <div class="list">
                            @foreach ($day->schedules as $schedule)
                                @if ($schedule->user_id === Auth::id())
                                    <div class="schedule-item" style="background-color: {{ $schedule->color }};">
                                        {{ $schedule->title }}
                                    </div>
                                @endif
                            @endforeach

                            @foreach ($zoomMeetings as $zoomMeeting)
                                @php
                                    $zoomMeetingDate = \Carbon\Carbon::parse($zoomMeeting->date);
                                @endphp

                                @if ($day->date == $zoomMeetingDate->toDateString())
                                    @if ($zoomMeeting->creator_id === Auth::id())
                                        <div class="zoomMeeting-item" style="background-color:#99d0d1; color: #58898a;">
                                            {{ $zoomMeeting->title_zoom }}
                                        </div>
                                    @elseif($zoomMeeting->invitedUsers->pluck('id')->contains(Auth::id()))
                                        <div class="zoomMeeting-item" style="background-color:#ffa500; color: #9c6502;">
                                            "{{ $zoomMeeting->title_zoom }}" <br>
                                            <strong>Creator: {{ $zoomMeeting->creator->name }}.</strong>
                                        </div>
                                    @endif
                                @endif
                            @endforeach
                        </div>
                    </div>
            @endforeach
        </div>
    </div>
    </div>
    <script>
        let currentYear = {{ $year }};

        function monthPicker() {
            const popup = document.getElementById('month-picker-popup');
            popup.style.display = (popup.style.display === 'none') ? 'block' : 'none';
        }

        function changeYear(direction) {
            currentYear += direction;
            document.getElementById('popup-year').innerText = currentYear;
        }

        function goToMonth(month) {
            window.location.href = `/calendar/${month}/${currentYear}`;
        }

        document.addEventListener('click', function(e) {
            const popup = document.getElementById('month-picker-popup');
            const display = document.querySelector('.month-year-display');
            if (!popup.contains(e.target) && !display.contains(e.target)) {
                popup.style.display = 'none';
            }
        });
    </script>

</x-app-layout>
