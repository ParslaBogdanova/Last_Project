/**
 * Closes the schedule details popup by hiding the container.
 */
function closeScheduleDetails() {
    const detailsContainer = document.getElementById('scheduleDetailsContainer');
    detailsContainer.classList.remove('visible');
}



/**
 * Sends a DELETE request to remove a specific schedule.
 *
 * @param {Event} event - The event triggered by clicking delete.
 * @param {number} scheduleId - The ID of the schedule to delete.
 */
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
            location.reload();
        } 
    })
}

//------------------------------------BLOCKED DAY---------------------------------------------

/**
 * Toggles visibility of the block options dropdown.
 */
document.getElementById('blockDropdownBtn').addEventListener('click', function() {
    const dropdownOptions = document.getElementById('dropdownOptions');
    dropdownOptions.style.display = dropdownOptions.style.display === 'none' || dropdownOptions.style.display === '' ? 'block' : 'none';
});



/**
 * Shows the block form and hides the dropdown.
 */
document.getElementById('blockDayBtn')?.addEventListener('click', function() {
    const blockForm = document.getElementById('blockForm');
    blockForm.style.display = 'block'; 
    document.getElementById('dropdownOptions').style.display = 'none'; 
});



/**
 * Handles block form submission, alters button behavior, and appends reason text.
 */
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



/**
 * Toggles the block form's visibility.
 */
function toggleBlockForm() {
    const blockForm = document.getElementById('blockForm');
    blockForm.style.display = blockForm.style.display === 'none' ? 'block' : 'none';
}

//---------------------------------ZOOM MEETING------------------------------------------------

/**
 * Displays the Zoom meeting form.
 */
document.getElementById('createZoomMeeting')?.addEventListener('click', function () {
    const zoomForm = document.getElementById('zoomForm');
    zoomForm.style.display = 'block';
    document.getElementById('dropdownOptions').style.display = 'none'; 
});




/**
 * Toggles the Zoom form's visibility.
 */
function toggleBlockForm() {
    const zoomForm = document.getElementById('zoomForm');
    zoomForm.style.display = zoomForm.style.display === 'none' ? 'block' : 'none';
}



/**
 * Hides Zoom meeting detail container.
 */
function closeZoomMeetingDetails() {
    const detailsContainer = document.getElementById('zoomMeetingDetailsContainer');
    detailsContainer.classList.remove('visible');
}



/**
 * Deletes a Zoom meeting via AJAX, with confirmation prompt.
 *
 * @param {Event} event - The click event.
 * @param {number} zoomMeetingId - ID of the Zoom meeting.
 */
function deleteZoomMeeting(event, zoomMeetingId) {
    event.stopPropagation();

    if (!confirm("Are you sure you want to cancel this meeting?")) {
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
            location.reload();
        }
    })
}

//---------------------------CLOSE FORMS------------------------------------------------------

/**
 * Hides the blocked day form.
 */
function closeBlockedDays() {
    document.getElementById('blockForm').style.display = 'none';
}



/**
 * Hides the Zoom form.
 */
function closeZoomForm() {
    document.getElementById('zoomForm').style.display = 'none';
}



/**
 * Validates Zoom form times before submitting.
 */
document.getElementById('zoomForm').addEventListener('submit', function(event) {
    const startTime = document.getElementById('start_time').value;
    const endTime = document.getElementById('end_time').value;

    if (startTime >= endTime) {
        event.preventDefault();
        alert('"End time" must be after start time and within the same day!');
    }
});




