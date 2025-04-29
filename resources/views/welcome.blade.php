<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ asset('css/welcome.css') }}">

    <title>Laravel</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
    @endif
</head>

<body>

    <header class="header">

        @if (Route::has('login'))
            <nav class="nav-links">
                @auth
                    <a href="{{ url('/dashboard') }}">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="login">Log in</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="register">Register</a>
                    @endif
                @endauth
            </nav>
        @endif
    </header>

    <main class="image-border" style="background-image: url('{{ asset('image/Coffee.jpg') }}');">
        <div class="image-content">
            <h2>Welcome to the Zoom Meeting scheduler 'Demo'</h2>
            <div>
                Create Zoom meetings with ease with no time limit or payments. Includes scheduling, fill up your
                own calendar, availability
                checks, and more.
            </div>
        </div>
    </main>

    <div class="spacer"></div>

    <main class="card-section">
        <section class="card">
            <h3>Meeting Flexibility</h3>
            <div class="info">
                Leave and rejoin meetings freely — they're active until the set end time.
            </div>
        </section>
        <section class="card">
            <h3>Unavailable Messages</h3>
            <div class="info">
                Get notified if friends/coworkers already part of the future/during meetings or unavailable due to
                reasons.
            </div>
        </section>
        <section class="card">
            <h3>Calendar View</h3>
            <div class="info">
                See all your meetings & own schedules, and availability at a glance.
            </div>
        </section>
        <section class="card-warning">
            <h3> Warning/Reminder</h3>
            <div class="info-warning">
                Please don't use your real email/password. This 'demo' is not secured, yet.
            </div>
        </section>
    </main>
    <footer class="footer">
        Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})
    </footer>
</body>


</html>
