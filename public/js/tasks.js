// Get references to the task creation button and form
const createTaskButton = document.getElementById('create-task-button');
const createTaskForm = document.getElementById('create-task-form');



/**
 * Toggles the visibility of the task creation form when the button is clicked.
 */
createTaskButton.addEventListener('click', () => {
    createTaskForm.classList.toggle('hidden');
});



/**
 * Handles checking/unchecking a task checkbox.
 * Toggles the task's completed state both visually and via server update.
 *
 * @param {HTMLInputElement} checkbox - The checkbox that was toggled.
 * @param {number} taskId - The ID of the task being updated. {number} - data type of the parameter
 */
function checkingTasks(checkbox, taskId) {
    const completed = checkbox.checked;

     // Find the task DOM element by its data attribute
    const taskItem = document.querySelector(`[data-task-id="${taskId}"]`);
    if (taskItem) {
        taskItem.classList.toggle('completed', completed);
    }

     // Send an AJAX PATCH request to update the task's completed status
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