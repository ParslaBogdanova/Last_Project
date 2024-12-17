<x-app-layout>
    <style>
        /* General Styles */
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f0f0;
            color: #333;
            margin: 0;
            padding: 0;
        }

        h1 {
            margin-top: 30px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            font-size: 40px;
            font-family: Sans-Serif;
            color: #A50028;
            /* Disco red */
        }

        .form-group {
            margin-top: 10px;
        }

        .text-input {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            padding: 10px;
            border: 2px solid #A50028;
            /* Disco red */
            border-radius: 30px;
            background-color: white;
        }

        .info-input {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 90%;
            margin-left: 5%;
            padding: 10px;
            border: 2px solid #A50028;
            border-radius: 30px;
            background-color: white;
        }

        .input-error {
            color: #949494;
            margin-top: 4px;
        }

        .button {
            background-color: #A50028;
            /* Disco red */
            display: flex;
            font-size: 14px;
            justify-content: center;
            align-items: center;
            color: white;
            padding: 10px 30px;
            border-radius: 30px;
            margin-top: 20px;
            margin-bottom: 30px;
            width: 90%;
            margin-left: 5%;
            font-family: sans-serif;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .button:hover {
            background-color: #c91440;
        }

        .link {
            color: #A50028;
            text-decoration: none;
            margin-top: 16px;
            font-weight: bold;
        }

        .link:hover {
            text-decoration: underline;
            color: #c91440;
        }

        .name-group {
            display: flex;
            gap: 2px;
            margin-top: 16px;
            margin-bottom: 25px;
        }

        /* Disco Button */
        .disco-button {
            background-color: #ff0066;
            /* Bright pinkish-red */
            padding: 12px 40px;
            border-radius: 50px;
            font-weight: bold;
            color: white;
            font-size: 16px;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            transition: all 0.3s ease;
        }

        .disco-button:hover {
            background-color: #ff3385;
            transform: scale(1.1);
        }

        /* Flashy Background */
        .disco-background {
            background: linear-gradient(45deg, #ff0066, #ff6699, #cc33cc, #ff33cc);
            background-size: 300% 300%;
            animation: discoAnimation 3s ease infinite;
        }

        @keyframes discoAnimation {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        /* Full-Height Sidebar */
        .sidebar {
            height: 100vh;
            /* Full height */
            display: flex;
            flex-direction: column;
            padding-top: 20px;
        }

        /* Sidebar Contents */
        .sidebar .text-lg {
            font-size: 20px;
            color: white;
        }

        /* Dropdown Styles */
        .dropdown-menu {
            display: none;
            position: absolute;
            background-color: #fff;
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
            z-index: 1;
            min-width: 160px;
            padding: 8px 0;
            border-radius: 4px;
            margin-top: 5px;
            right: 0;
        }

        .dropdown-item {
            padding: 8px 16px;
            text-align: left;
            color: #333;
        }

        .dropdown-item:hover {
            background-color: #f1f1f1;
            cursor: pointer;
        }

        /* Chat Area */
        .chat-area {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100vh;
            background-color: #f4f0f0;
            padding: 20px;
        }

        .chat-messages {
            flex: 1;
            overflow-y: scroll;
            margin-bottom: 20px;
        }

        .message-input {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: auto;
        }

        .message-input input {
            flex: 1;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 25px;
            font-size: 14px;
        }

        .message-input button {
            padding: 12px 20px;
            background-color: #A50028;
            /* Disco red */
            border: none;
            color: white;
            border-radius: 25px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .message-input button:hover {
            background-color: #c91440;
        }
    </style>

    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="sidebar w-1/4 bg-gray-800 text-white p-4 disco-background">
            <div class="text-lg font-semibold mb-4 flex items-center justify-between">
                <span>Chats</span>
                <!-- Dropdown Icon (three dots) -->
                <button id="dropdownButton" class="text-white">
                    <i class="fa fa-ellipsis-h text-xl"></i>
                </button>
            </div>

            <!-- Dropdown Menu -->
            <div id="dropdownMenu" class="dropdown-menu">
                <div class="dropdown-item" id="newMessageOption">New Message</div>
                <div class="dropdown-item" id="newGroupOption">New Group</div>
            </div>

            <!-- Contact List -->
            <div id="contactList" class="space-y-2 mt-4">
                @foreach ($contacts as $contact)
                    <div class="contact-item cursor-pointer" data-contact-id="{{ $contact->id }}">
                        <span class="font-medium">{{ $contact->name }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Chat Area -->
        <div class="flex-1 bg-gray-50 chat-area">
            <!-- Header -->
            <div id="chatHeader" class="chat-header mb-4">
                <span class="font-medium" id="contactName"></span>
            </div>

            <!-- Chat Messages -->
            <div class="chat-messages" id="chatMessages">
                <!-- Chat messages will be dynamically loaded here -->
            </div>

            <!-- Message Input -->
            <div class="message-input">
                <input type="text" id="messageInput" placeholder="Type a message">
                <button id="sendMessageButton">
                    <i class="fa fa-send"></i>
                </button>
            </div>
        </div>
    </div>

    <script>
        // Toggle Dropdown Menu visibility
        document.getElementById('dropdownButton').addEventListener('click', function() {
            const dropdown = document.getElementById('dropdownMenu');
            dropdown.classList.toggle('hidden');
        });

        // Show the modal for New Message and New Group
        document.getElementById('newMessageOption').addEventListener('click', function() {
            alert('New Message clicked!');
            document.getElementById('dropdownMenu').classList.add('hidden');
        });

        document.getElementById('newGroupOption').addEventListener('click', function() {
            alert('New Group clicked!');
            document.getElementById('dropdownMenu').classList.add('hidden');
        });

        // Handle contact selection
        document.querySelectorAll('.contact-item').forEach(function(contact) {
            contact.addEventListener('click', function() {
                const contactId = contact.dataset.contactId;
                fetch(`/messages/chat/${contactId}`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('chatHeader').classList.remove('hidden');
                        document.getElementById('contactName').textContent = data.contact.name;

                        const chatMessages = document.getElementById('chatMessages');
                        chatMessages.innerHTML = '';
                        data.messages.forEach(message => {
                            const messageElement = document.createElement('div');
                            messageElement.classList.add('message', message.is_sender ? 'sent' :
                                'received');
                            messageElement.innerHTML = `
                                <div class="message-content">${message.content}</div>
                            `;
                            chatMessages.appendChild(messageElement);
                        });
                    });
            });
        });

        // Handle sending a new message
        document.getElementById('sendMessageButton').addEventListener('click', function() {
            const messageText = document.getElementById('messageInput').value.trim();
            const contactId = document.getElementById('contactName').dataset.contactId;

            if (messageText) {
                fetch(`/messages/send`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        },
                        body: JSON.stringify({
                            contact_id: contactId,
                            message: messageText
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        const chatMessages = document.getElementById('chatMessages');
                        const messageElement = document.createElement('div');
                        messageElement.classList.add('message', 'sent');
                        messageElement.innerHTML = `
                        <div class="message-content">${data.message}</div>
                    `;
                        chatMessages.appendChild(messageElement);
                        document.getElementById('messageInput').value = ''; // Clear input
                    });
            }
        });
    </script>
</x-app-layout>
