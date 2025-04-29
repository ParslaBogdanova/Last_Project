<x-app-layout>

    <head>
        <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    </head>
    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="container-log-in">
                <h2 style="font-size: 20px;">
                    {{ __("You're logged in!") }}
                </h2>
            </div>
        </div>
    </div>

    <main class="main-container">
        <div class="container">
            <div class="warning">
                <h2>Warning</h2>
                <div class="warning-info">
                    Please do not use your real email or password!
                    This project currently does not implement privacy or security features.
                </div>
            </div>
            <div class="project-info">
                <h2>What is this project about?</h2>
                <div class="project-info-info">
                    This project is still a work in progress - a 'DEMO', you might say.
                    It's focused on Zoom meetings and personal planning schedules.
                    Each user can create their own planning list. Zoom meetings don't have a time limit,
                    other than the 'start_time' and 'end_time' defined by the user.
                    The goal is to make it easy for users to create and manage meetings with friends,
                    students, teachers - you name it.
                </div>
            </div>
            <div class="calendar">
                <h2>Calendar Fun Facts</h2>
                <div class="calendar-info">
                    There are two types of schedules: Zoom meetings and basic schedules. <br>
                    Basic schedules appear only on your calendar and are private. <br>
                    Zoom meetings appear in <span style="color:#99d0d1;">light blue</span>,
                    which means you are the creator and have invited others. <br>
                    The color <span style="color:#ffa500;">orange</span> means you have been invited
                    to someone else’s meeting. These appear in both your task and calendar views. <br><br>
                    You can only edit or delete your own Zoom meetings.
                    Meetings are shared only with those who are invited. The system checks availability
                    before accepting invitations.
                </div>
            </div>
            <div class="zoom-meeting">
                <h2>How to Create a Zoom Meeting</h2>
                <div class="zoom-meeting-info">
                    First, choose a date for your meeting with friends or coworkers.
                    Add a title and topic, then select the start and end times.
                    A Zoom meeting won't be created unless at least one friend is invited. <br><br>
                    Once created, a notification and reminder are sent to the invited friend.
                    It will appear on their calendar, along with a notification showing who invited them.
                    A countdown will also be shown for both the inviter and creator.<br><br>
                    <strong>Note:</strong> Users may be unavailable or block specific dates. <br>
                    A “blocked day” means the user marked themselves unavailable for the entire day
                    (with a reason provided). Blocked days are visible on calendars and will remove
                    any affected meetings. Only the user can unblock themselves.
                </div>
            </div>
            <div class="future-plans">
                <h2>Future Plans</h2>
                <div class="future-plans-info">
                    Planned features include:
                    <br>- Friends list
                    <br>- Policies
                    <br>- Privacy and safety improvements
                    <br>- Group chat in messages
                    <br>- Group chat profile pictures
                    <br>- User profile pictures
                    <br>- In-meeting group and 1-on-1 chat
                    <br>- Emojis
                </div>
            </div>
            <div class="reminder">
                <h2>Reminder</h2>
                <div class="reminder-info">
                    Reminder: Laravel has announced version 12. I'm still using version 11,
                    which prevents me from fully implementing WebSocket functionality for Zoom calls. <br><br>
                    I did use WebRTC(communication between browsers), so users can toggle their mic and camera - but
                    they can't
                    yet hear or see each other. That requires sharing IP addresses, which
                    WebSockets support. <br><br>
                    Why haven't I upgraded Laravel? <br>
                    Because I only became aware of the new features a month later.
                    With deadlines and exams approaching, I accepted that this project wouldn't be 100% complete.
                    I also feared breaking the project due to possible PHP changes tied to the upgrade.
                </div>
            </div>
        </div>
    </main>

</x-app-layout>
