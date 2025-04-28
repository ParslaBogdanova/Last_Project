<x-app-layout>

    <head>
        <link rel="stylesheet" href="{{ asset('css/messages_index.css') }}">
    </head>

    <div style="display: flex; height: calc(100vh - 64px);">
        <div class="sidebar">
            <h1>Chats &#10000</h1>
            <div id="userList">
                @foreach ($users as $user)
                    <div class="user-item">
                        <a href="/messages/{{ $user->id }}" class="user-link">
                            <span>{{ $user->name }}</span>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
        <div id="writingArea" class="writing-area">
        </div>
    </div>
</x-app-layout>
