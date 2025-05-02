<x-app-layout>

    <head>
        <link rel="stylesheet" href="{{ asset('css/messages_show.css') }}">
        <script src="{{ asset('js/messages_show.js') }}"></script>
    </head>

    <div style="display: flex; height: calc(100vh - 64px);">
        <div class="sidebar">
            <h1>Chats &#10000</h1>
            <div id="userList">
                @foreach ($users as $user)
                    <div class="user-item {{ $user->id == $receiver_id ? 'active' : '' }}">
                        <a href="{{ route('messages.show', $user->id) }}" class="user-link">
                            <span>{{ $user->name }}</span>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>

        <div id="writingArea" class="writing-area {{ $receiver_id ? 'active' : '' }}">
            <div class="writing-content" id="chatMessages">

                @if ($messages->isNotEmpty())
                    @php
                        $lastDate = null;
                    @endphp
                    @foreach ($messages as $message)
                        @php
                            $currentDate = \Carbon\Carbon::parse($message->created_at)->format('d M Y');
                        @endphp

                        @if ($currentDate !== $lastDate)
                            <div class="message-date-header">{{ $currentDate }}</div>
                            @php $lastDate = $currentDate; @endphp
                        @endif

                        <div class="message-container {{ $message->sender_id == Auth::id() ? 'sent' : 'received' }}"
                            id="message-{{ $message->id }}">
                            <div class="user-name">{{ $message->sender->name }}</div>
                            <div class="message">
                                <div class="message-content">{{ $message->content }}</div>

                                @foreach ($message->files as $file)
                                    <div class="message-file" id="message-file-{{ $message->id }}">
                                        @php $url = Storage::url($file->file_path); @endphp

                                        @if (Str::endsWith($file->file_path, ['jpg', 'jpeg', 'png', 'gif']))
                                            <a href="{{ $url }}" target="_blank">
                                                <img src="{{ $url }}" alt="Sent Image"
                                                    style="max-width: 200px; cursor: pointer;" class="thumbnail-image"
                                                    data-full-url="{{ $url }}">
                                            </a>
                                        @else
                                            <a href="{{ $url }}" download="{{ $file->file_title }}">
                                                {{ $file->file_title }}
                                            </a>
                                        @endif
                                    </div>
                                @endforeach


                            </div>

                            <div class="message-time">
                                {{ \Carbon\Carbon::parse($message->created_at)->format('h:i A') }}
                            </div>

                            @if ($message->sender_id == Auth::id())
                                <div class="dropdown">
                                    <button class="dropdown-toggle">...</button>
                                    <div class="dropdown-menu">
                                        <button onclick="editMessage({{ $message->id }})">Edit</button>
                                        <button onclick="deleteMessage({{ $message->id }})">Delete</button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>

            <div class="file-preview-container">
                <div id="filePreviewArea" class="file-preview-area"></div>
            </div>

            <div class="writing-input">
                <input type="file" id="fileInput" name="file" accept=".jpg,.jpeg,.png,.pdf,.docx,.txt"
                    style="display: none;" multiple />
                <input type="text" id="messageInput" placeholder="Type a message...">
                <label for="fileInput" class="file-label" style=" cursor: pointer;">&#9993;</label>
                <button onclick="sendMessage()" id="sendButton">&#10148;</button>
            </div>
        </div>
    </div>

    <meta name="receiver-id" content="{{ $receiver_id ?? '' }}">

    <div id="imageModal" class="image-modal">
        <div class="modal-content">
            <img id="modalImage" src="">
        </div>
    </div>

    <script>
        let selectedUserId = {{ $receiver_id ?? 'null' }};
    </script>

</x-app-layout>
