<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <style>
        body {
            background-color: #322E38;
            font-family: Sans-Serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            justify-content: center;
            align-items: center;
        }


        .container {
            background-color: white;
            width: 100%;
            max-width: 500px;
            margin-top: 24px;
            padding: 24px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 20px;
        }

        .info-border {
            width: 100%;
            max-width: 100%;
            margin-top: 24px;
            box-shadow: 5px 4px 8px rgba(0, 0, 0, 0.1);
            background-size: cover;
            background-image: url('https://wallpapercave.com/wp/wp8149198.jpg');
            background-position: center;
            padding: 24px;
            border-radius: 20px;
            position: relative;
            color: white;
        }

        .info-border::before {
            position: absolute;
            background: rgba(0, 0, 0, 0.4);
            border-radius: 20px;
            z-index: 20;
        }

        .info-content {
            position: relative;
            z-index: 2;
            text-align: center;
        }

        .title {
            font-size: 30px;
            margin-bottom: 20px;
        }

        .info {
            font-family: sans-serif;
            margin-left: 10%;
            margin-right: 10%;
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>

    <div class="container">
        {{ $slot }}
    </div>
</body>

</html>
