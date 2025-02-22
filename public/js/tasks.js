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
    });
}