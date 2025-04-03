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

//---------------------------------------------------------------------------------

document.getElementById('blockDropdownBtn').addEventListener('click', function() {
    const dropdownOptions = document.getElementById('dropdownOptions');
    dropdownOptions.style.display = dropdownOptions.style.display === 'none' || dropdownOptions.style.display === '' ? 'block' : 'none';
});

document.getElementById('blockDayBtn')?.addEventListener('click', function() {
    const blockForm = document.getElementById('blockForm');
    blockForm.style.display = 'block'; 
    document.getElementById('dropdownOptions').style.display = 'none'; 
});

document.getElementById('block-form')?.addEventListener('submit', function(event) {
    event.preventDefault();

    const reason = document.getElementById('reason').value;

    if (reason) {
        const blockDropdownBtn = document.getElementById('blockDropdownBtn');
        blockDropdownBtn.innerText = 'Unblock This Day';
        blockDropdownBtn.removeEventListener('click', toggleBlockForm);

        blockDropdownBtn.addEventListener('click', function() {
            document.getElementById('unblock-form').submit();
        });

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

document.getElementById('createZoomMeeting')?.addEventListener('click', function () {
    const zoomForm = document.getElementById('zoomForm');
    zoomForm.style.display = 'block';
    document.getElementById('dropdownOptions').style.display = 'none'; 
});

    
function toggleBlockForm() {
    const zoomForm = document.getElementById('zoomForm');
    zoomForm.style.display = zoomForm.style.display === 'none' ? 'block' : 'none';
}

function closeZoomMeetingDetails() {
    const detailsContainer = document.getElementById('zoomMeetingDetailsContainer');
    detailsContainer.classList.remove('visible');
}

function deleteZoomMeeting(event, zoomMeetingId) {
    event.stopPropagation();

    if (!confirm("Are you sure you want to delete this Zoom Meeting?")) {
        return;
    }

    const url = `/calendar/{{ $month }}/{{ $year }}/{{ $day->id }}/zoom_meetings/${zoomMeetingId}`;
    fetch(url, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
    }).then(response => {
        if (response.ok) {
            alert('Zoom Meeting deleted successfully.');
            location.reload();
        } else {
            alert("Failed to delete the zoom meeting.");
        }
    }).catch(error => {
        alert("An error occurred while trying to delete the zoom meeting.");
    });
}




