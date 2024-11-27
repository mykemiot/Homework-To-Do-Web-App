const allSideMenu = document.querySelectorAll('#sidebar .side-menu.top li a');

allSideMenu.forEach(item=> {
	const li = item.parentElement;

	item.addEventListener('click', function () {
		allSideMenu.forEach(i=> {
			i.parentElement.classList.remove('active');
		})
		li.classList.add('active');
	})
});

// TOGGLE SIDEBAR
const menuBar = document.querySelector('#content nav .bx.bx-menu');
const sidebar = document.getElementById('sidebar');

menuBar.addEventListener('click', function () {
	sidebar.classList.toggle('hide');
})

const searchButton = document.querySelector('#content nav form .form-input button');
const searchButtonIcon = document.querySelector('#content nav form .form-input button .bx');
const searchForm = document.querySelector('#content nav form');

searchButton.addEventListener('click', function (e) {
	if(window.innerWidth < 576) {
		e.preventDefault();
		searchForm.classList.toggle('show');
		if(searchForm.classList.contains('show')) {
			searchButtonIcon.classList.replace('bx-search', 'bx-x');
		} else {
			searchButtonIcon.classList.replace('bx-x', 'bx-search');
		}
	}
})

if(window.innerWidth < 768) {
	sidebar.classList.add('hide');
} else if(window.innerWidth > 576) {
	searchButtonIcon.classList.replace('bx-x', 'bx-search');
	searchForm.classList.remove('show');
}


window.addEventListener('resize', function () {
	if(this.innerWidth > 576) {
		searchButtonIcon.classList.replace('bx-x', 'bx-search');
		searchForm.classList.remove('show');
	}
})

const handleDropdownClicked = (event) => {
	event.stopPropagation();
	const dropdownMenu = document.getElementById("dropdown-menu");
	toggleDropdownMenu(!dropdownMenu.classList.contains("open"));
};

const toggleDropdownMenu = (isOpen) => {
const dropdownMenu = document.getElementById("dropdown-menu");
const dropdownIcon = document.getElementById("dropdown-icon");

if (isOpen) {
    dropdownMenu.classList.add("open");
} else {
    dropdownMenu.classList.remove("open");
}

dropdownIcon.innerText = dropdownMenu.classList.contains("open")
    ? "close"
    : "expand_more";
};

document.body.addEventListener("click", () => toggleDropdownMenu());

const calendarMonth = document.getElementById('calendar-month');
const calendarDates = document.getElementById('calendar-dates');
const prevMonthButton = document.getElementById('prev-month');
const nextMonthButton = document.getElementById('next-month');
const eventModal = document.getElementById('event-modal');
const closeModalButton = document.getElementById('close-modal');
const eventForm = document.getElementById('event-form');
const eventTitleInput = document.getElementById('event-title');
const eventDateInput = document.getElementById('event-date');

let currentDate = new Date();
let events = JSON.parse(localStorage.getItem('events')) || [];

// Utility function to format date as YYYY-MM-DD
function formatDate(date) {
    return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
}

// Render calendar based on current date
function renderCalendar(date) {
    calendarDates.innerHTML = '';
    calendarMonth.textContent = date.toLocaleString('default', { month: 'long', year: 'numeric' });

    const firstDayOfMonth = new Date(date.getFullYear(), date.getMonth(), 1).getDay();
    const daysInMonth = new Date(date.getFullYear(), date.getMonth() + 1, 0).getDate();

    for (let i = 0; i < firstDayOfMonth; i++) {
        const emptyDiv = document.createElement('div');
        calendarDates.appendChild(emptyDiv);
    }

    for (let day = 1; day <= daysInMonth; day++) {
        const dateDiv = document.createElement('div');
        dateDiv.textContent = day;
        const dateString = formatDate(new Date(date.getFullYear(), date.getMonth(), day));

        dateDiv.addEventListener('click', () => openModal(dateString));

        const eventForDay = events.find(event => event.date === dateString);
        if (eventForDay) {
            const eventLabel = document.createElement('span');
            eventLabel.className = 'event-label';
            eventLabel.textContent = eventForDay.title;

            // Create delete button
            const deleteButton = document.createElement('button');
            deleteButton.className = 'delete-button';
            deleteButton.textContent = '×'; // Close symbol
            deleteButton.onclick = (e) => {
                e.stopPropagation(); // Prevent triggering the openModal function
                deleteEvent(dateString);
            };

            eventLabel.appendChild(deleteButton);
            dateDiv.appendChild(eventLabel);
        }

        calendarDates.appendChild(dateDiv);
    }
}

// Open modal for adding event
function openModal(date) {
    eventDateInput.value = date;
    eventModal.style.display = 'block';
    eventTitleInput.focus();
}

// Close the modal
function closeModal() {
    eventModal.style.display = 'none';
    eventForm.reset(); // Clear form data
}

// Save new event
function saveEvent(e) {
    e.preventDefault(); // Prevents the default form submission

    const title = eventTitleInput.value.trim();
    const desc = eventDescInput.value.trim();
    const date = eventDateInput.value;

    if (!title || !date) {
        alert("Event title and date are required.");
        return;
    }

    // Send data to PHP script using Fetch API
    fetch('add_event.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ title, desc, date }), // Send title, description, and date as JSON
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Clear localStorage if you want to rely on fresh data from the database
            localStorage.removeItem('events');
            fetchEvents(); // Fetch the updated list of events from the database
            renderCalendar(currentDate); // Re-render the calendar
            closeModal();
        } else {
            alert(data.message || "Failed to save event.");
        }
    })
    .catch(error => {
        console.error("Fetch Error:", error);
        alert("An error occurred while saving the event.");
    });
}

// Function to fetch events from the database
function fetchEvents() {
    fetch('get_event.php')  // A new PHP file that will fetch events from the database
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                events = data.events; // Update the events array with the data from the database
                localStorage.setItem('events', JSON.stringify(events)); // Optionally store in localStorage for offline use
                renderCalendar(currentDate); // Re-render the calendar with the updated events
            } else {
                alert("Failed to fetch events.");
            }
        })
        .catch(error => {
            console.error("Fetch Error:", error);
            alert("An error occurred while fetching events.");
        });
}

// Function to delete an event
function deleteEvent(date) {
    // Send delete request to the backend
    fetch('delete_event.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ date }), // Send date as JSON
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Clear localStorage if you want to rely on fresh data from the database
            localStorage.removeItem('events');
            fetchEvents(); // Fetch the updated list of events from the database
            renderCalendar(currentDate); // Re-render the calendar with the updated events
        } else {
            alert(data.message || "Failed to delete event.");
        }
    })
    .catch(error => {
        console.error("Delete Fetch Error:", error);
        alert("An error occurred while deleting the event.");
    });
}

// Event Listeners for Navigation and Modal Controls
prevMonthButton.addEventListener('click', () => {
    currentDate.setMonth(currentDate.getMonth() - 1);
    renderCalendar(currentDate);
});

nextMonthButton.addEventListener('click', () => {
    currentDate.setMonth(currentDate.getMonth() + 1);
    renderCalendar(currentDate);
});

closeModalButton.addEventListener('click', closeModal);
window.addEventListener('click', (e) => {
    if (e.target == eventModal) closeModal();
});

// Initial Render
renderCalendar(currentDate);
  

// Select all sidebar links and main content sections
const sidebarLinks = document.querySelectorAll('.side-menu a');
const contentSections = document.querySelectorAll('.main-content');

// Function to display the correct section and update the active link
sidebarLinks.forEach(link => {
link.addEventListener('click', function(e) {
    e.preventDefault();  // Prevent the default link behavior

    // Hide all content sections
    contentSections.forEach(section => {
    section.style.display = 'none';
    });

    // Remove active class from all links and add to the clicked one
    sidebarLinks.forEach(link => {
    link.parentElement.classList.remove('active');
    });
    this.parentElement.classList.add('active');

    // Get the target section ID from the data-section attribute
    const targetSectionId = this.getAttribute('data-section');
    console.log('Target Section:', targetSectionId); // Debugging line to check

    // Show the target section if it exists
    const targetSection = document.getElementById(targetSectionId);
    if (targetSection) {
    targetSection.style.display = 'block';  // Display the section
    console.log('Section found and displayed:', targetSectionId); // Debugging line
    } else {
    console.warn('No section found for ID:', targetSectionId); // Debugging line
    }
});
});

// Initialize by showing the dashboard content only
document.getElementById('dashboard-content').style.display = 'block';


// noti and profile
// Open and close Notification Modal
function openNotificationModal() {
    document.getElementById("notificationModal").style.display = "block";
}

function closeNotificationModal() {
    document.getElementById("notificationModal").style.display = "none";
}

// Function to open the modal
function openProfileModal() {
    document.getElementById("profileModal").style.display = "block";
}

// Function to close the modal
function closeProfileModal() {
    document.getElementById("profileModal").style.display = "none";
}

// Close modal when clicking outside of it
window.onclick = function(event) {
    const modal = document.getElementById("profileModal");
    if (event.target === modal) {
        modal.style.display = "none";
    }
}


// Close the modal when clicking outside the modal content
window.onclick = function(event) {
    const modal = document.getElementById("profileModal");
    if (event.target === modal) {
        closeProfileModal();
    }
};

// Optional: Close the modal when clicking outside of the modal content
window.onclick = function(event) {
    const modal = document.getElementById("notificationModal");
    if (event.target === modal) {
        closeNotificationModal();
    }
};

function logout() {
    fetch('logout.php')
        .then(response => {
            if (response.ok) {
                // Redirect to the landing page after successful logout
                window.location.href = 'landingpage.php';
            } else {
                console.error('Logout failed');
            }
        })
        .catch(error => console.error('Error:', error));
}

// button logout
document.getElementById("logout-button").addEventListener("click", function() {
    window.location.href = "logout.php";
});

//for project modal
function openProjectForm() {
    document.getElementById("projectFormModal").style.display = "block";
}

function closeProjectForm() {
    document.getElementById("projectFormModal").style.display = "none";
}

function openEditProjectForm(projectId, projectName, projectDesc, projectDuedate, projectStatus) {
    document.getElementById('edit_project_id').value = projectId;
    document.getElementById('edit_project_name').value = projectName;
    document.getElementById('edit_project_desc').value = projectDesc;
    document.getElementById('edit_project_duedate').value = projectDuedate;
    document.getElementById('edit_project_status').value = projectStatus;
    document.getElementById('editProjectModal').style.display = 'block';
}

function openEditProjectForm(projectId) {
    fetch(`get_project_details.php?project_id=${projectId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById("edit_project_id").value = projectId;
            document.getElementById("edit_project_name").value = data.project_name || '';
            document.getElementById("edit_project_desc").value = data.project_desc || '';
            document.getElementById("edit_project_duedate").value = data.project_duedate || '';
            document.getElementById("currentFileName").textContent = data.file_name || 'No file attached';

            // Set the project status
            document.getElementById("edit_project_status").value = data.project_status || '';

            // Open the modal
            document.getElementById("editProjectModal").style.display = "block";
        })
        .catch(error => console.error("Error loading project data:", error));
}


function closeEditProjectForm() {
    document.getElementById("editProjectModal").style.display = "none";
}

function deleteProject() {
    const projectId = document.getElementById("edit_project_id").value;
    if (confirm("Are you sure you want to delete this project? This action cannot be undone.")) {
        fetch(`delete_project.php?project_id=${projectId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload(); // Refresh the page
                    alert("Project deleted successfully.");
                } else {
                    alert("Failed to delete the project: " + (data.error || "Unknown error."));
                }
            })
            .catch(error => {
                console.error("Error deleting project:", error);
                alert("An error occurred: " + error.message);
            });
    }
}

function closeDeleteConfirmModal() {
    document.getElementById('deleteConfirmModal').style.display = 'none';
}

function confirmDeleteProject() {
    const projectId = document.getElementById("edit_project_id").value;
    if (confirm("Are you sure you want to delete this project? This action cannot be undone.")) {
        fetch(`delete_project.php?project_id=${projectId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Project deleted successfully.");
                    location.reload(); // Refresh the page
                } else {
                    alert("Failed to delete the project. Please try again.");
                }
            })
            .catch(error => console.error("Error deleting project:", error));
    }
}

//draggable content (project form)
const modal = document.querySelector('#projectFormModal .modal-content');
const header = document.querySelector('.modal-header'); // Only the header is draggable

let isDragging = false, offsetX = 0, offsetY = 0, mouseX, mouseY;

// Start dragging when mousedown on the header
header.addEventListener('mousedown', function (e) {
    isDragging = true;
    offsetX = modal.offsetLeft;
    offsetY = modal.offsetTop;
    mouseX = e.clientX;
    mouseY = e.clientY;
    document.addEventListener('mousemove', dragModal);
    document.addEventListener('mouseup', stopDragModal);
});

// Dragging function
function dragModal(e) {
    if (isDragging) {
        const dx = e.clientX - mouseX;
        const dy = e.clientY - mouseY;
        modal.style.left = offsetX + dx + 'px';
        modal.style.top = offsetY + dy + 'px';
        modal.style.position = 'absolute';
    }
}

// Stop dragging
function stopDragModal() {
    isDragging = false;
    document.removeEventListener('mousemove', dragModal);
    document.removeEventListener('mouseup', stopDragModal);
} 

// Task
// Open the Task Form Modal
function openTaskForm() {
    document.getElementById('taskFormModal').style.display = 'block';
}

// Close the Task Form Modal
function closeTaskForm() {
    document.getElementById('taskFormModal').style.display = 'none';
}

// Open Edit Task Form
function openEditTaskForm(taskId) {
    // Fetch task details with AJAX
    fetch('get_task.php?task_id=' + taskId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Populate the form with task data
                document.getElementById("edit_task_id").value = data.task.task_id;
                document.getElementById("edit_task_name").value = data.task.task_name;
                document.getElementById("edit_task_desc").value = data.task.task_desc;
                document.getElementById("edit_task_duedate").value = data.task.task_duedate;
                document.getElementById("edit_task_status").value = data.task.task_status;
                document.getElementById("edit_project_id").value = data.task.project_id;

                // Show the modal
                document.getElementById("editTaskModal").style.display = "block";
            } else {
                alert("Failed to load task details.");
            }
        })
        .catch(error => console.error('Error:', error));
}

// Close the Edit Task Form
function closeEditTaskForm() {
    document.getElementById("editTaskModal").style.display = "none";
}

// Delete Task
function deleteTask(taskId) {
    if (confirm("Are you sure you want to delete this task?")) {
        // Make an AJAX request to delete the task
        fetch('delete_task.php?task_id=' + taskId, {
            method: 'GET'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Task deleted successfully.");
                location.reload(); // Refresh the page to show updated task list
            } else {
                alert("Error deleting task.");
            }
        })
        .catch(error => console.error('Error:', error));
    }
}

// Group Assignment
// Function to open the project group form modal
function openProjectGroupForm() {
    document.getElementById("projectGroupFormModal").style.display = "block";
}

// Function to close the project group form modal
function closeProjectGroupForm() {
    document.getElementById("projectGroupFormModal").style.display = "none";
}

// Optional: Close the modal if the user clicks outside of it
window.onclick = function(event) {
    var modal = document.getElementById("projectGroupFormModal");
    if (event.target == modal) {
        modal.style.display = "none";
    }
}


// JavaScript function to open the Edit Project form and populate the fields
function openEditProjectGroupForm(groupId) {
    // Create a new XMLHttpRequest to fetch project data from the server
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'get_group_project.php?group_id=' + groupId, true); // Make a GET request to fetch data
    
    // On successful response from the server
    xhr.onload = function () {
        if (xhr.status === 200) {
            // Parse the returned JSON data
            var projectData = JSON.parse(xhr.responseText);
            console.log(projectData); // Log the project data for debugging

            // Check if project data is valid
            if (projectData && !projectData.error) {
                // Populate the form fields with the project data
                document.getElementById('edit_group_id').value = projectData.group_id;
                document.getElementById('edit_gproject_name').value = projectData.project_name;
                document.getElementById('edit_gproject_desc').value = projectData.project_desc;
                document.getElementById('edit_due_date').value = projectData.due_date;
                document.getElementById('edit_members_needed').value = projectData.members_needed;

                // Show the modal
                document.getElementById('editProjectGroupFormModal').style.display = 'block';
            } else {
                console.error('Error:', projectData.error); // Log error if data is not valid
                alert('Error loading project data.');
            }
        } else {
            console.error('Request failed with status:', xhr.status); // Log the status if the request failed
            alert('Failed to fetch project details.');
        }
    };

    // Send the request to the server
    xhr.send();
}

// Function to close the modal
function closeEditProjectGroupForm() {
    document.getElementById('editProjectGroupFormModal').style.display = 'none';
}

function deleteProjectGroup(groupId) {
    // Confirm deletion with the user
    if (confirm("Are you sure you want to delete this project?")) {
        // If confirmed, send the request to delete the project
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'delete_group_project.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        // Send the group_id to the server to process the deletion
        xhr.onload = function () {
            if (xhr.status === 200) {
                // If successful, alert the user and reload the page to reflect changes
                alert("Project deleted successfully.");
                location.reload(); // Reload the page to reflect the changes
            } else {
                // If an error occurred, alert the user
                alert("Failed to delete the project. Please try again.");
            }
        };

        // Send the group_id as a POST parameter
        xhr.send('group_id=' + encodeURIComponent(groupId));
    }
}

function requestToJoin(group_id) {
    fetch('request_join.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ group_id: group_id })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert(data.message);
            document.getElementById(`joinButton${group_id}`).disabled = true; // Disable button after request
            document.getElementById(`joinButton${group_id}`).innerText = "Request Sent";
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while sending the join request.');
    });
}


function approveJoinRequest(request_id) {
    fetch('update_request_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ request_id: request_id, action: 'approve' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert(data.message);
            location.reload(); // Reload to update the join requests
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the request status.');
    });
}

function rejectJoinRequest(request_id) {
    fetch('update_request_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ request_id: request_id, action: 'reject' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert(data.message);
            location.reload(); // Reload to update the join requests
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the request status.');
    });
}


