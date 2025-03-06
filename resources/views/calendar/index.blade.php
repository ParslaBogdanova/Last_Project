<x-app-layout>

    <head>
        <link rel="stylesheet" href="{{ asset('css/calendar_index.css') }}">
    </head>

    <div class="container">
        <div class="month-year-display">
            {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}
        </div>

        <div class="month-navigation">
            <form action="{{ route('calendar.index', ['month' => $prevMonth, 'year' => $prevYear]) }}" method="GET">
                <button type="submit">Previous</button>
            </form>
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
                <a href="{{ route('calendar.show', ['month' => $month, 'year' => $year, 'day_id' => $day->id]) }}">
                    <div class="calendar-day {{ $isToday ? 'today' : '' }} {{ $isBlocked ? 'blocked' : '' }}">
                        <span class="day-number">{{ \Carbon\Carbon::parse($day->date)->day }}</span>
                        <div class="schedules">
                            @foreach ($day->schedules as $schedule)
                                <div class="schedule-item" style="background-color: {{ $schedule->color }};">
                                    {{ $schedule->title }}
                                </div>
                            @endforeach
                        </div>
                        <div class="zoomMeetings">
                            @foreach ($day->zoomMeetings as $zoomMeeting)
                                <div class="zoomMeeting-item" style="background-color:darkslategray;">
                                    {{ $zoomMeeting->title_zoom }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</x-app-layout>
