<x-app-layout>
    <style>
        .main-content {
            padding: 1rem;
            display: flex;
            overflow-x: auto;
            gap: 1rem;
            flex-grow: 1;
        }

        .info-card {
            flex: 0 0 300px;
            height: 450px;
            background-color: #644951;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: center;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            padding: 1rem;
            box-sizing: border-box;
        }

        .unread-messages {
            flex: 0 0 300px;
            height: 450px;
            background-color: white;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: center;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            padding: 1rem;
            box-sizing: border-box;
        }

        .task-info {
            flex: 0 0 300px;
            height: 450px;
            background-color: white;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: center;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            padding: 1rem;
            box-sizing: border-box;
        }

        .task-list {
            margin-top: 1rem;
            width: 100%;
            height: 300px;
            overflow-y: auto;
        }

        .task-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px;
            border-bottom: 1px solid #e0e0e0;
        }

        .checkbox {
            border-color: #b5b4b3;
        }

        .task-list a {
            color: #b5b4b3;
            text-decoration: none;
        }

        .delete-btn {
            color: #e3342f;
            font-size: 1rem;
            cursor: pointer;
            padding: 5px;
        }

        .delete-btn:hover {
            color: #ff0000;
        }

        .create-task {
            margin-top: auto;
            padding: 0.5rem 1rem;
            color: #fc86a1;
            border-radius: 4px;
            text-decoration: none;
            text-align: center;
            cursor: pointer;
        }

        .completed a {
            text-decoration: line-through;
            color: #9e9e9e;
        }
    </style>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Tasks') }}
        </h2>
    </x-slot>

    <main class="main-content">
        <div class="info-card">
            <h1>More exchange messages</h1>
            <p>Like discord and other things</p>
            {{-- <a href="{{ url('login/discord') }}">
                <button>Login with Discord</button>
            </a> --}}
        </div>

        <div class="unread-messages">
            <h1>Unread messages</h1>
            <p>This will be after only i create a friends list.</p>
        </div>

        <div class="task-info">
            <h1>Task List</h1>
            <div class="task-list" id="task-list">
                @foreach ($tasks as $task)
                    <div class="task-item" data-task-id="{{ $task->id }}">

                        <input type="checkbox" class="rounded checkbox"
                            onchange="checkingTasks(this, {{ $task->id }})"
                            @if ($task->completed) checked @endif>

                        <a href="{{ route('tasks.show', $task->id) }}">
                            {{ $task->description }}
                        </a>
                        <form action="{{ route('tasks.destroy', $task->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-btn">X</button>
                        </form>

                    </div>
                @endforeach
            </div>

            <form id="create-task-form" class="task-form hidden" action="{{ route('tasks.store') }}" method="POST">
                @csrf
                <label for="description">Description:</label>
                <input type="text" id="description" name="description" placeholder="Description" required>
                <button type="submit">Save Task</button>
            </form>

            <a id="create-task-button" class="create-task">
                Create New Task
            </a>
        </div>
    </main>

    <script>
        const createTaskButton = document.getElementById('create-task-button');
        const createTaskForm = document.getElementById('create-task-form');

        createTaskButton.addEventListener('click', () => {
            createTaskForm.classList.toggle('hidden');
        });

        function checkingTasks(checkbox, taskId) {
            const completed = checkbox.checked;

            const taskItem = document.querySelector(`[data-task-id="${taskId}"]`);
            if (taskItem) {
                taskItem.classList.toggle('completed', completed);
            }

            fetch(`/tasks/${taskId}/update-completed`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    completed
                })
            }).catch(err => console.error('Error updating task status:', err));
        }
    </script>
</x-app-layout>
