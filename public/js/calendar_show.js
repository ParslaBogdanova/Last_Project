function closeScheduleDetails() {
    const detailsContainer = document.getElementById('scheduleDetailsContainer');
    detailsContainer.classList.remove('visible');
}

function deleteSchedule(event, scheduleId) {
    event.stopPropagation();
    const url = `/calendar/{{ $month }}/{{ $year }}/{{ $day->id }}/schedules/${scheduleId}`;
    fetch(url, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
    }).then(response => {
        if (response.ok) {
            alert('Schedule deleted successfully.');
            location.reload();
        } else {
            alert("Failed to delete the schedule.");
        }
    }).catch(error => {
        alert("An error occurred while trying to delete the schedule.");
    });
}

document.getElementById('blockDropdownBtn').addEventListener('click', function() {
    const dropdownOptions = document.getElementById('dropdownOptions');
    dropdownOptions.style.display = dropdownOptions.style.display === 'none' || dropdownOptions.style.display === '' ? 'block' : 'none';
});

document.getElementById('blockDayBtn').addEventListener('click', function() {
    const blockForm = document.getElementById('blockForm');
    blockForm.style.display = 'block'; 
    document.getElementById('dropdownOptions').style.display = 'none'; 
});

document.getElementById('block-form').addEventListener('submit', function(event) { 
    event.preventDefault();

    const reason = document.getElementById('reason').value;

    if (reason) {
        document.getElementById('blockDropdownBtn').innerText = 'Unblock This Day';
        document.getElementById('blockDropdownBtn').removeEventListener('click', toggleBlockForm);

        const blockedReasonDiv = document.createElement('div');
        blockedReasonDiv.classList.add('blocked-reason');
        blockedReasonDiv.innerHTML = `<strong>Reason for Blocking:</strong> ${reason}`;

        document.querySelector('.calendar-day-details').appendChild(blockedReasonDiv);
        this.submit();
    }
});

function toggleBlockForm() {
    const blockForm = document.getElementById('blockForm');
    blockForm.style.display = blockForm.style.display === 'none' ? 'block' : 'none';
}

//---------------------------------------------------------------------------------

document.getElementById('createZoomMeeting').addEventListener('click', function () {
    const zoomForm = document.getElementById('zoomForm');
    zoomForm.style.display = 'block';
    document.getElementById('dropdownOptions').style.display = 'none'; 
});

document.getElementById('zoom-form').addEventListener('submit', function(event) {
    event.preventDefault(); 

    const title_zoom = document.getElementById('title_zoom').value;
    const topic_zoom = document.getElementById('topic_zoom').value;
    const invited_users = Array.from(document.getElementById('invited_users').selectedOptions).map(option => option.value);
    const start_time = document.getElementById('start_time').value;
    const end_time = document.getElementById('end_time').value;

    console.log({ title_zoom, topic_zoom, invited_users, start_time, end_time });

});
