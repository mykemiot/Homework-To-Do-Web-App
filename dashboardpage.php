<?php
// Start the session at the very beginning of the file
session_start();
include("connection.php");

// Ensure student is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: signinpage.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// Fetch total student count
$sql = "SELECT COUNT(*) as total FROM student";
$result = mysqli_query($connect, $sql);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $totalStudents = $row['total'];
}

// Fetch information for the logged-in student
$studentInfo = "";
$query = "SELECT student_name, student_email, student_semester FROM student WHERE student_id = ?";
$stmt = $connect->prepare($query);
$stmt->bind_param("i", $student_id); 
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    $student = $result->fetch_assoc();
    $studentInfo = "<strong>Name:</strong> " . htmlspecialchars($student['student_name']) . "<br><br>" .
                   "<strong>Email:</strong> " . htmlspecialchars($student['student_email']) . "<br><br>" .
                   "<strong>Semester:</strong> " . htmlspecialchars($student['student_semester']) . "<br><br>";
} else {
    $studentInfo = "No information found.";
}

// Fetch project data for the logged-in student
$query = "
    SELECT p.project_id, p.project_name, p.project_desc, p.project_duedate, p.project_status, 
           f.file_name, f.file_path
    FROM project AS p
    LEFT JOIN file AS f ON p.project_id = f.project_id
    WHERE p.student_id = ?
";
$stmt = $connect->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$resultprojectdb = $stmt->get_result();

// Fetch tasks for the logged-in student
$query = "
    SELECT t.task_id, t.project_id, t.task_name, t.task_desc, t.task_duedate, t.task_status,
           p.project_name
    FROM task AS t
    LEFT JOIN project AS p ON t.project_id = p.project_id
    WHERE t.project_id IN (SELECT project_id FROM project WHERE student_id = ?)
";
$stmt = $connect->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$resulttaskdb = $stmt->get_result();

// Close the statement and connection
$stmt->close();
$connect->close();
?>

<?php
include("connection.php");

// Ensure the user is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: signinpage.php");
    exit();
}

$student_id = $_SESSION['student_id'];
$current_date = date('Y-m-d'); // Today's date

// Fetch projects with due dates within the next month or overdue
$projectQuery = "
    SELECT p.project_id, p.project_name, p.project_duedate 
    FROM project AS p
    WHERE p.student_id = ? 
      AND (p.project_duedate <= DATE_ADD(?, INTERVAL 1 MONTH) AND p.project_duedate >= ?)
";

$stmt = $connect->prepare($projectQuery);
$stmt->bind_param("iss", $student_id, $current_date, $current_date);
$stmt->execute();
$result = $stmt->get_result();

// Loop through the projects and push notifications
while ($project = $result->fetch_assoc()) {
    $project_id = $project['project_id'];
    $project_name = $project['project_name'];
    $project_duedate = $project['project_duedate'];

    // Check if a notification already exists for this project
    $checkNotificationQuery = "
        SELECT notification_id 
        FROM notification 
        WHERE student_id = ? AND project_id = ? AND is_read = 0
    ";
    $stmtCheck = $connect->prepare($checkNotificationQuery);
    $stmtCheck->bind_param("ii", $student_id, $project_id);
    $stmtCheck->execute();
    $checkResult = $stmtCheck->get_result();

    if ($checkResult->num_rows == 0) {
        // Insert a new notification
        $notificationText = "Reminder: Project '$project_name' is due on $project_duedate.";
        $insertNotificationQuery = "
            INSERT INTO notification (student_id, project_id, notification_text, notification_date, is_read) 
            VALUES (?, ?, ?, ?, 0)
        ";
        $stmtInsert = $connect->prepare($insertNotificationQuery);
        $stmtInsert->bind_param("iiss", $student_id, $project_id, $notificationText, $current_date);
        $stmtInsert->execute();
        $stmtInsert->close();
    }
    $stmtCheck->close();
}

$stmt->close();
?>

<?php
// Connect to the database
include("connection.php");

// Ensure the user is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: signinpage.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// Fetch unread notifications
$notificationQuery = "
    SELECT notification_id, notification_text, notification_date, is_read 
    FROM notification 
    WHERE student_id = ? 
    ORDER BY notification_date DESC
";

$stmtNotification = $connect->prepare($notificationQuery);
$stmtNotification->bind_param("i", $student_id);
$stmtNotification->execute();
$notificationsResult = $stmtNotification->get_result();

$notifications = [];
$notification_count = 0;

while ($row = $notificationsResult->fetch_assoc()) {
    $notifications[] = $row;
    if (!$row['is_read']) {
        $notification_count++;
    }
}

$stmtNotification->close();
?>

<!-- Fetching project -->
<?php
// Connect to database
include("connection.php");

// Ensure student is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: signinpage.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// Fetch projects for the logged-in student
$sql = "
    SELECT p.project_id, p.project_name, p.project_desc, p.project_duedate, p.project_status, 
           f.file_name, f.file_path
    FROM project AS p
    LEFT JOIN file AS f ON p.project_id = f.project_id
    WHERE p.student_id = ?
";
$stmt = $connect->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$resultproject = $stmt->get_result();

$stmt->close();
$connect->close();
?>

<!-- Fetching tasks -->
<?php
// Connect to database
include("connection.php");

// Ensure student is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: signinpage.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// Fetch tasks for the logged-in student, including project name and assigned student
$sql = "
    SELECT t.task_id, t.project_id, t.task_name, t.task_desc, t.task_duedate, t.task_status,
           p.project_name
    FROM task AS t
    LEFT JOIN project AS p ON t.project_id = p.project_id
    WHERE t.project_id IN (SELECT project_id FROM project WHERE student_id = ?)
";

$stmt = $connect->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$resulttask = $stmt->get_result();

$stmt->close();
$connect->close();
?>


<!-- Fetching Group Projects -->
<?php
// Connect to database
include("connection.php");

// Ensure student is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: signinpage.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// Fetch only group projects where the logged-in student is the leader
$sqlGroupProjects = "
    SELECT gp.group_id, gp.project_name, gp.leader_id, gp.members_needed, gp.project_desc, gp.due_date, gp.status,
           s.student_name AS leader_name
    FROM group_project AS gp
    INNER JOIN student AS s ON gp.leader_id = s.student_id
    WHERE gp.leader_id = ?
";

$stmtGroupProjects = $connect->prepare($sqlGroupProjects);
$stmtGroupProjects->bind_param("i", $student_id); // Binding the leader's ID (logged-in student)
$stmtGroupProjects->execute();
$resultGroupProjects = $stmtGroupProjects->get_result();
$stmtGroupProjects->close();

$connect->close();
?>

<!-- Shows Available Project -->
<?php
// Connect to database
include("connection.php");

// Ensure student is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: signinpage.php");
    exit();
}

$student_id = $_SESSION['student_id'];
// Fetch available group projects (those not assigned to the current student)
$sqlAvailableProjects = "
    SELECT gp.group_id, gp.project_name, gp.leader_id, gp.members_needed, gp.project_desc, gp.due_date, gp.status,
           s.student_name AS leader_name,
           (SELECT COUNT(*) FROM group_members WHERE group_members.group_id = gp.group_id) AS current_members
    FROM group_project AS gp
    LEFT JOIN student AS s ON gp.leader_id = s.student_id
    WHERE gp.group_id NOT IN (SELECT group_id FROM group_members WHERE student_id = ?)  -- exclude groups the student is a member of
    AND (SELECT COUNT(*) FROM group_members WHERE group_members.group_id = gp.group_id) < gp.members_needed  -- ensure members_needed is not full
";

$stmtAvailableProjects = $connect->prepare($sqlAvailableProjects);
$stmtAvailableProjects->bind_param("i", $student_id); // Binding student_id
$stmtAvailableProjects->execute();
$resultAvailableProjects = $stmtAvailableProjects->get_result();
$stmtAvailableProjects->close();

// Retrieve all lecturers' information
$sql_lecturers = "SELECT name, email, phone FROM lecture";
$result_lecturers = $connect->query($sql_lecturers);

$connect->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<!-- Boxicons -->
	<link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
	<!-- My CSS -->
	<link rel="stylesheet" href="DBstyle.css">
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">

	<title>Homework To-Do</title>
</head>
	<body>

		<!-- SIDEBAR -->
		<section id="sidebar">
			<a href="#" class="brand">
				<div class="bx">
					<img src="img/backuplogo.png" alt="" style="width: 24px; height: 24px;">
				</div>
				<span class="text">To-Do!!</span>
			</a>
			<ul class="side-menu top">
				<li class="active">
					<a href="#" data-section="dashboard-content">
						<i class='bx bxs-dashboard'></i>
						<span class="text">Dashboard</span>
					</a>
				</li>
				<li>
					<a href="#" data-section="project-content">
						<i class='bx bxs-grid'></i>
						<span class="text">Project</span>
					</a>
				</li>
				<li>
					<a href="#" data-section="task-content">
						<i class='bx bxs-notepad'></i>
						<span class="text">Task</span>
					</a>
				</li>
				<li>
					<a href="#" data-section="groupasg-content">
						<i class='bx bxs-group'></i>
						<span class="text">Group Assignment</span>
					</a>
				</li>
				<li>
					<a href="#" data-section="group-content">
						<i class='bx bxs-group'></i>
						<span class="text">Group</span>
					</a>
				</li>
				<li>
					<a href="discussion.php" data-section="discussion-content">
						<i class='bx bxs-message-dots'></i>
						<span class="text">Discussion</span>
					</a>
				</li>
			</ul>
			<ul class="side-menu">
				<li>
					<a href="#" data-section="setting-content">
						<i class='bx bxs-cog' ></i>
						<span class="setting">Settings</span>
					</a>
				</li>
				<li>
					<a href="#" data-section="lecturer-content">
						<i class='bx bxs-group' ></i>
						<span class="setting">Lecture Contact</span>
					</a>
				</li>
				<li>
					<a href="#" data-section="logout-content">
						<i class='bx bxs-log-out-circle'></i>
						<span class="logout">Logout</span>
					</a>
				</li>
			</ul>
		</section>
		<!-- SIDEBAR -->


		<!-- CONTENT -->
		<section id="content">
			<!-- NAVBAR -->
			<nav>
				<i class='bx bx-menu'></i>
				<form id="search-form">
					<div class="form-input">
						<input type="search" id="search-query" placeholder="Search..." />
						<button type="submit" class="search-btn"><i class='bx bx-search'></i></button>
					</div>
				</form>

				<!-- Notification Icon -->
				<div class="notification" onclick="openNotificationModal()">
					<i class='bx bxs-bell'></i>
					<span class="num"><?php echo $notification_count; ?></span> <!-- Show notification count -->
				</div>

				<!-- Profile Icon -->
				<div id="profileIcon" class="profile" onclick="openProfileModal()">
					<i class='bx bxs-user'></i> <!-- Person icon -->
				</div>
			</nav>

			<!-- Modal for Search Results -->
			<div id="search-modal" class="modal">
				<div class="modal-content">
					<span id="close-modal" class="close">&times;</span>
					<h3>Search Results</h3>
					<div id="search-results"></div>
				</div>
			</div>

			<!-- Notification Modal -->
			<div id="notificationModal" class="modal notification-modal">
				<div class="modal-content notification-modal-content">
					<span class="close notification-modal-close" onclick="closeNotificationModal()">&times;</span>
					<h2 class="notification-modal-title">Notifications</h2><br>
					<div id="notificationList" class="notification-list">
						<?php if (!empty($notifications)) : ?>
							<?php foreach ($notifications as $notification) : ?>
								<div class="notification-item <?php echo $notification['is_read'] ? 'notification-read' : 'notification-unread'; ?>">
									<p class="notification-text"><?php echo htmlspecialchars($notification['notification_text']); ?></p>
									<small class="notification-date"><?php echo date("F j, Y, g:i a", strtotime($notification['notification_date'])); ?></small>
								</div>
							<?php endforeach; ?>
						<?php else : ?>
							<p class="notification-empty">No new notifications.</p>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<!-- Profile Modal -->
			<div id="profileModal" class="modal">
				<div class="modal-content">
					<span class="close" onclick="closeProfileModal()">&times;</span>
					<h2>Student Profile</h2><br>
					<p id="studentInfo"><?php echo $studentInfo; ?></p> <!-- Display student information -->
				</div>
			</div>
			<!-- NAVBAR -->

			<!-- MAIN -->
			<main>
				<!-- Dashboard Section -->
				<div id="dashboard-content" class="main-content" style="display: none;">
					<div class="head-title">
						<div class="left">
							<h2>Dashboard</h2><br>
							<p>Welcome Back! <?php echo htmlspecialchars($student['student_name']); ?></p>
						</div>
					</div>
			
						<?php
							// Connect to the database
							include("connection.php");

							// Total Students
							$totalStudentsQuery = "SELECT COUNT(*) AS total_students FROM student";
							$totalStudentsResult = mysqli_query($connect, $totalStudentsQuery);
							$totalStudentsRow = mysqli_fetch_assoc($totalStudentsResult);
							$totalStudents = $totalStudentsRow['total_students'];

							// Total Projects
							$totalProjectsQuery = "SELECT COUNT(*) AS total_projects FROM project";
							$totalProjectsResult = mysqli_query($connect, $totalProjectsQuery);
							$totalProjectsRow = mysqli_fetch_assoc($totalProjectsResult);
							$totalProjects = $totalProjectsRow['total_projects'];
						?>

						<!-- Display the counts -->
						<ul class="box-info">
							<li>
								<i class='bx bxs-user'></i>
								<span class="text">
									<h3><?php echo $totalStudents; ?></h3>
									<p>Total Students</p>
								</span>
							</li>
							<li>
								<i class='bx bxs-folder'></i>
								<span class="text">
									<h3><?php echo $totalProjects; ?></h3>
									<p>Total Projects</p>
								</span>
							</li>
						</ul>

					<!-- Calendar Event Table -->
					<div class="calendar-container">
						<div class="calendar-header">
							<button id="prev-month"><i class='bx bx-left-arrow-alt'></i></button>
							<h3 id="calendar-month"></h3>
							<button id="next-month"><i class='bx bx-right-arrow-alt'></i></button>
						</div>
						<div class="calendar">
							<div class="calendar-days">
								<span>Sun</span>
								<span>Mon</span>
								<span>Tue</span>
								<span>Wed</span>
								<span>Thu</span>
								<span>Fri</span>
								<span>Sat</span>
							</div>
							<div id="calendar-dates" class="calendar-dates"></div>
						</div>
					</div>
					
					<!-- Modal for Adding Event -->
					<div id="event-modal" class="modal">
						<div class="modal-content">
							<!-- <span id="close-modal" class="close">&times;</span> -->
							<h3>Add New Event</h3>
							<form action="add_event.php" method="POST" id="event-form">
								<h2>Coming Soon...</h2>
							</form>
						</div>
					</div>
					
					<!-- Display project and task information -->
					<div class="table-data">
						<div class="order">
							<div class="head">
								<h3>Projects</h3>
								<i class='bx bx-filter' id="filter-icon" onclick="toggleProjectFilter()" title="Sort Projects"></i>
							</div>
							<table>
								<thead>
									<tr>
										<th>Project Title</th>
										<th>Due Date</th>
										<th>Status</th>
									</tr>
								</thead>
								<tbody id="projects-table-body">
									<?php if ($resultprojectdb->num_rows > 0): ?>
										<?php while ($row = $resultprojectdb->fetch_assoc()): ?>
											<tr data-project-name="<?= htmlspecialchars($row['project_name']); ?>">
												<td><p><?= htmlspecialchars($row['project_name']); ?></p></td>
												<td><?= htmlspecialchars($row['project_duedate']); ?></td>
												<?php $statusClass = ($row['project_status'] == 'Completed') ? 'completed' : 'pending'; ?>
												<td><span class="status <?= $statusClass; ?>"><?= htmlspecialchars($row['project_status']); ?></span></td>
											</tr>
										<?php endwhile; ?>
									<?php else: ?>
										<tr>
											<td colspan="3">No projects available.</td>
										</tr>
									<?php endif; ?>
								</tbody>
							</table>
						</div>

						<div class="todo">
							<div class="head">
								<h3>Tasks</h3>
								<i class="bx bx-filter" id="filter-icon" onclick="toggleFilter()" title="Sort tasks"></i>
							</div>
							<ul class="todo-list" id="todo-list">
								<?php if ($resulttaskdb->num_rows > 0): ?>
									<?php while ($row = $resulttaskdb->fetch_assoc()): ?>
										<?php 
										$taskClass = ($row['task_status'] == 'Completed') ? 'completed' : 'not-completed'; 
										?>
										<li class="<?= $taskClass; ?>" data-task-name="<?= htmlspecialchars($row['task_name']); ?>">
											<p><?= htmlspecialchars($row['task_name']); ?></p>
											<i class='bx bx-dots-vertical-rounded'></i>
										</li>
									<?php endwhile; ?>
								<?php else: ?>
									<li>No tasks available.</li>
								<?php endif; ?>
							</ul>
						</div>
					</div>
				</div>
			
				<!-- Project Section (Initially Hidden) -->
				<div id="project-content" class="main-content" style="display: none;">
					<h2>Project Details</h2><br>
			
					<!-- Card Container -->
					<div class="card-columns">
						<?php if ($resultproject->num_rows > 0): ?>
							<?php while ($row = $resultproject->fetch_assoc()): ?>
								<div class="card">
									<div class="card-body">
										<h5 class="card-title"><?= htmlspecialchars($row['project_name']); ?></h5><br>
										<p class="card-text"><?= htmlspecialchars($row['project_desc']); ?></p><br>
										<p class="card-text"><small class="text-muted">Due: <?= htmlspecialchars($row['project_duedate']); ?></small></p><br>
										
										<!-- File Information -->
										<?php if (!empty($row['file_name'])): ?>
											<p class="card-text">Attached File: 
												<a href="<?= htmlspecialchars($row['file_path']); ?>" target="_blank">
													<?= htmlspecialchars($row['file_name']); ?>
												</a>
											</p><br>
										<?php else: ?>
											<p class="card-text">No files attached.</p><br>
										<?php endif; ?>
										
										<!-- Project Status Badge -->
										<p class="card-text">
											<span class="badge badge-<?= $row['project_status'] == 'Completed' ? 'success' : ($row['project_status'] == 'Ongoing' ? 'warning' : 'secondary'); ?>">
												<?= htmlspecialchars($row['project_status']); ?>
											</span>
										</p><br>
										<div class="task-actions">
											<button class="btn btn-primary" onclick="openEditProjectForm(<?= $row['project_id'] ?>)">Edit Task</button>
										</div>
									</div>
								</div>
							<?php endwhile; ?>
						<?php else: ?>
							<p>No projects available.</p>
						<?php endif; ?>
					</div>


					<!-- + New Project Button -->
					<button id="newProjectBtn" onclick="openProjectForm()">+ New Project</button>

					<!-- Project Form Modal (Initially Hidden) -->
					<div id="projectFormModal" class="modal" style="display: none;">
						<div class="modal-content">
							<span class="close" onclick="closeProjectForm()">&times;</span>
							<h2>New Project</h2>
							<form action="add_project.php" method="POST" enctype="multipart/form-data">
								<label for="project_name">Project Name:</label>
								<input type="text" id="project_name" name="project_name" required><br>

								<label for="project_desc">Description:</label>
								<textarea id="project_desc" name="project_desc" required></textarea><br>

								<label for="project_duedate">Due Date:</label>
								<input type="date" id="project_duedate" name="project_duedate" required><br>

								<label for="project_status">Status:</label>
								<select id="project_status" name="project_status">
									<option value="Pending">Pending</option>
									<option value="Ongoing">Ongoing</option>
									<option value="Completed">Completed</option>
								</select><br>

								<label for="project_file">Add File (PDF, DOCX):</label>
								<input type="file" id="project_file" name="project_file" accept=".pdf,.docx"><br>

								<button type="submit">Save Project</button>
							</form>
						</div>
					</div>

					<!-- Edit -->
					<div id="editProjectModal" class="modal" style="display: none;">
						<div class="modal-content">
							<h2>Edit Project</h2>
							<form id="editProjectForm" method="POST" enctype="multipart/form-data" action="edit_project.php">
								<input type="hidden" name="project_id" id="edit_project_id">
								
								<label>Project Title:</label>
								<input type="text" name="project_name" id="edit_project_name" placeholder="Enter project title" required><br>
								
								<label>Project Description:</label>
								<textarea name="project_desc" id="edit_project_desc" placeholder="Enter project description" required></textarea><br>
								
								<label>Due Date:</label>
								<input type="date" name="project_duedate" id="edit_project_duedate" required><br>

								<label for="project_status">Status:</label>
								<select id="edit_project_status" name="project_status">
									<option value="Pending">Pending</option>
									<option value="Ongoing">Ongoing</option>
									<option value="Completed">Completed</option>
								</select><br>

								<p>Current File: <span id="currentFileName">No file attached</span></p>
								<label>Upload New File (optional):</label>
								<input type="file" name="project_file"><br>
								
								<button type="submit" name="save_changes">Save Changes</button>
								<button type="button" onclick="deleteProject()">Delete Project</button>
								<button type="button" onclick="closeEditProjectForm()">Cancel</button>
							</form>
						</div>
					</div>
				</div>

				<!-- Task Section (Initially Hidden) -->
				<div id="task-content" class="main-content" style="display: none;">
					<h1>Tasks</h1><br>

					<!-- Card Container -->
					<div class="container">
						<?php if ($resulttask->num_rows > 0): ?>
							<?php while ($task = $resulttask->fetch_assoc()): ?>
								<div class="task-section">
									<!-- Card to show available tasks -->
									<div class="card">
										<div class="card-body">
											<!-- Task Information -->
											<h5 class="card-title"><?= htmlspecialchars($task['task_name']); ?></h5><br>
											<p class="card-text"><?= htmlspecialchars($task['task_desc']); ?></p><br>
											<p class="card-text"><small class="text-muted">Due: <?= htmlspecialchars($task['task_duedate']); ?></small></p><br>

											<!-- Task Status Badge -->
											<p class="card-text">
												<span class="badge 
													<?= $task['task_status'] === 'Completed' ? 'badge-success' : 
													($task['task_status'] === 'In Progress' ? 'badge-warning' : 'badge-secondary'); ?>">
													<?= htmlspecialchars($task['task_status']); ?>
												</span>
											</p><br>

											<!-- Project Name and Assigned Students -->
											<p class="card-text">Project: <?= htmlspecialchars($task['project_name']); ?></p><br>

											<!-- Action Buttons -->
											<div class="task-actions">
												<button class="btn btn-primary" onclick="openEditTaskForm(<?= $task['task_id'] ?>)">Edit Task</button>
												<button class="btn btn-danger" onclick="deleteTask(<?= $task['task_id'] ?>)">Delete Task</button>
											</div>
										</div>
									</div>
								</div>
							<?php endwhile; ?>
						<?php else: ?>
							<p>No tasks available.</p>
						<?php endif; ?>
					</div>

					<!-- + New Task Button -->
					<button id="newTaskBtn" onclick="openTaskForm()">+ New Task</button>

					<!-- Task Form Modal (Initially Hidden) -->
					<div id="taskFormModal" class="modal" >
						<div class="modal-content">
							<span class="close" onclick="closeTaskForm()">&times;</span>
							<h2>New Task</h2>
							<form action="add_task.php" method="POST" enctype="multipart/form-data">
								<!-- Hidden field for task_id, will be used for editing a task -->
								<input type="hidden" id="task_id" name="task_id">

								<label for="task_name">Task Name:</label>
								<input type="text" id="task_name" name="task_name" required><br>

								<label for="task_desc">Description:</label>
								<textarea id="task_desc" name="task_desc" required></textarea><br>

								<label for="task_duedate">Due Date:</label>
								<input type="date" id="task_duedate" name="task_duedate" required><br> 

								<label for="task_status">Status:</label>
								<select id="task_status" name="task_status" required>
									<option value="Not Started">Not Started</option>
									<option value="In Progress">In Progress</option>
									<option value="Completed">Completed</option>
								</select>

								<label for="project_id">Project:</label>
								<select id="project_id" name="project_id" required>
									<!-- Options will be dynamically loaded -->
									<?php
									// Fetch projects from the database to populate the project dropdown
									$sql = "SELECT project_id, project_name FROM project WHERE student_id = ?";
									$stmt = $connect->prepare($sql);
									$stmt->bind_param("i", $student_id); // Use logged-in student ID
									$stmt->execute();
									$result = $stmt->get_result();
									while ($project = $result->fetch_assoc()) {
										echo "<option value='" . $project['project_id'] . "'>" . htmlspecialchars($project['project_name']) . "</option>";
									}
									$stmt->close();
									?>
								</select><br>

								<button type="submit">Add Task</button>
							</form>
						</div>
					</div>

					<!-- Edit task -->
					<div id="editTaskModal" class="modal" style="display: none;">
						<div class="modal-content">
							<span class="close" onclick="closeEditTaskForm()">&times;</span>
							<h2>Edit Task</h2>
							<form action="update_task.php" method="POST">
								<input type="hidden" name="task_id" id="edit_task_id">

								<label>Task Title:</label>
								<input type="text" name="task_name" id="edit_task_name" placeholder="Enter task title" required><br>

								<label>Task Description:</label>
								<textarea name="task_desc" id="edit_task_desc" placeholder="Enter task description" required></textarea><br>

								<label>Due Date:</label>
								<input type="date" name="task_duedate" id="edit_task_duedate" required><br>

								<label for="task_status">Status:</label>
								<select id="edit_task_status" name="task_status">
									<option value="Not Started">Not Started</option>
									<option value="In Progress">In Progress</option>
									<option value="Completed">Completed</option>
								</select><br>

								<label>Project:</label>
								<select id="edit_project_id" name="project_id" required>
									<?php
									// Fetch projects from the database to populate the project dropdown
									$sql = "SELECT project_id, project_name FROM project WHERE student_id = ?";
									$stmt = $connect->prepare($sql);
									$stmt->bind_param("i", $student_id); // Use logged-in student ID
									$stmt->execute();
									$result = $stmt->get_result();
									while ($project = $result->fetch_assoc()) {
										echo "<option value='" . $project['project_id'] . "'>" . htmlspecialchars($project['project_name']) . "</option>";
									}
									$stmt->close();
									?>
								</select><br>

								<button type="submit" name="save_changes">Save Changes</button>
							</form>
						</div>
					</div>
				</div>

				<!-- Group Assignment Section (Initially Hidden) -->
				<div id="groupasg-content" class="main-content" style="display: none;">
					<h1>Group Assignments</h1><br>
					
					<!-- Group Project Card Container -->
					<div class="container">
						<?php if ($resultGroupProjects->num_rows > 0): ?>
							<?php while ($group = $resultGroupProjects->fetch_assoc()): ?>
								<div class="project-section">
									<div class="card">
										<div class="card-body">
											<h5 class="card-title"><?= htmlspecialchars($group['project_name']); ?></h5><br>
											<p class="card-text"><?= htmlspecialchars($group['project_desc']); ?></p><br>
											<p class="card-text"><small class="text-muted">Due: <?= htmlspecialchars($group['due_date']); ?></small></p><br>
											<p class="card-text"><strong>Members Needed:</strong> <?= htmlspecialchars($group['members_needed']); ?></p><br>

											<!-- Action Buttons for Project -->
											<div class="project-actions">
												<button class="btn btn-primary" onclick="openEditProjectGroupForm(<?= htmlspecialchars($group['group_id']) ?>)">Edit Project</button>
												<button class="btn btn-danger" onclick="deleteProjectGroup(<?= htmlspecialchars($group['group_id']) ?>)">Delete Project</button>
											</div>

											<!-- Join Requests Table -->
											<br><h2>Join Requests:</h2><br>
											<?php
												// Ensure this code is only run within the loop where $group['group_id'] is defined
												if (isset($group['group_id'])) {
													$group_id = $group['group_id'];

													// Prepare and execute the query to fetch join requests for the current group project
													$joinRequestsQuery = "
														SELECT gr.request_id, gr.student_id, s.student_name 
														FROM group_join_requests gr
														JOIN student s ON gr.student_id = s.student_id
														WHERE gr.group_id = ? AND gr.request_status = 'pending'
													";

													$stmt = $connect->prepare($joinRequestsQuery);
													$stmt->bind_param("i", $group_id);
													$stmt->execute();
													$joinRequestsResult = $stmt->get_result();

													// Check if there are join requests
													if ($joinRequestsResult->num_rows > 0) {
														echo '<table class="table">
																<thead>
																	<tr>
																		<th>Student Name</th>
																		<th>Actions</th>
																	</tr>
																</thead>
																<tbody>';

														// Display each join request in the table
														while ($joinRequest = $joinRequestsResult->fetch_assoc()) {
															echo '<tr>
																	<td>' . htmlspecialchars($joinRequest['student_name']) . '</td>
																	<td>
																		<button class="btn btn-success" onclick="approveJoinRequest(' . $joinRequest['request_id'] . ')">Approve</button>
																		<button class="btn btn-danger" onclick="rejectJoinRequest(' . $joinRequest['request_id'] . ')">Reject</button>
																	</td>
																</tr>';
														}

														echo '</tbody>
															</table>';
													} else {
														echo "<p>No join requests for this project.</p>";
													}

													$stmt->close();
												} else {
													echo "<p>No group project selected.</p>";
												}
											?>
										</div>
									</div>
								</div>
							<?php endwhile; ?>
						<?php else: ?>
							<p>No group projects available.</p>
						<?php endif; ?>
					</div>


					<!-- + New GA Button -->
					<br><button id="newGABtn" onclick="openProjectGroupForm()">+ New Group Assignment</button><br>

					<!-- Available Projects for non-members -->
					<div class="container">
						<br><br><h4>Available Projects to Join</h4><br>
						<?php if ($resultAvailableProjects->num_rows > 0): ?>
							<?php while ($available = $resultAvailableProjects->fetch_assoc()): ?>
								<div class="group-project-section">
									<div class="card">
										<div class="card-body">
											<h5 class="card-title"><?= htmlspecialchars($available['project_name']); ?></h5><br>
											<p class="card-text">Description: <?= htmlspecialchars($available['project_desc']); ?></p><br>
											<p class="card-text"><small class="text-muted">Due: <?= htmlspecialchars($available['due_date']); ?></small></p><br>

											<!-- Project Status Badge -->
											<p class="card-text">
												<span class="badge 
													<?= $available['status'] === 'Completed' ? 'badge-success' : 
													($available['status'] === 'In Progress' ? 'badge-warning' : 'badge-secondary'); ?>">
													<?= htmlspecialchars($available['status']); ?>
												</span>
											</p><br>

											<!-- Leader Information -->
											<p class="card-text">Leader: <?= htmlspecialchars($available['leader_name']); ?></p><br>

											<!-- Request to Join Button -->
											<div class="group-project-actions">
												<?php if ($available['current_members'] < $available['members_needed']): ?>
													<button id="joinButton<?= $available['group_id'] ?>" class="btn btn-success" 
															onclick="requestToJoin(<?= $available['group_id'] ?>)">
														Request to Join
													</button>
												<?php else: ?>
													<button class="btn btn-secondary" disabled>
														Group Full
													</button>
												<?php endif; ?>
											</div>
										</div>
									</div>
								</div>
							<?php endwhile; ?>
						<?php else: ?>
							<p>No available projects to join.</p>
						<?php endif; ?>
					</div><br>

					<!-- Project Group Form Modal (Initially Hidden) -->
					<div id="projectGroupFormModal" class="modal" style="display: none;">>
						<div class="modal-content">
							<span class="close" onclick="closeProjectGroupForm()">&times;</span>
							<h2>Create New Group Project</h2>
							<form action="add_group_project.php" method="POST" enctype="multipart/form-data">
								<!-- Hidden field for group_id, used for editing a group project if needed -->
								<input type="hidden" id="group_id" name="group_id">

								<label for="project_name">Project Name:</label>
								<input type="text" id="project_name" name="project_name" required><br>

								<label for="project_desc">Project Description:</label>
								<textarea id="project_desc" name="project_desc" required></textarea><br>

								<label for="due_date">Due Date:</label>
								<input type="date" id="due_date" name="due_date" required><br>

								<label for="members_needed">Number of Members Needed:</label>
								<input type="number" id="members_needed" name="members_needed" min="1" required><br>

								<button type="submit">Create</button>
							</form>
						</div>
					</div>

					<!-- Edit Project Group Form Modal -->
					<div id="editProjectGroupFormModal" class="modal" style="display: none;">
						<div class="modal-content">
							<span class="close" onclick="closeEditProjectGroupForm()">&times;</span>
							<h2>Edit Group Project</h2>
							<form action="edit_group_project.php" method="POST">
								<input type="hidden" id="edit_group_id" name="group_id">

								<label for="edit_gproject_name">Project Name:</label>
								<input type="text" id="edit_gproject_name" name="project_name" required><br>

								<label for="edit_gproject_desc">Project Description:</label>
								<textarea id="edit_gproject_desc" name="project_desc" required></textarea><br>

								<label for="edit_due_date">Due Date:</label>
								<input type="date" id="edit_due_date" name="due_date" required><br>

								<label for="edit_members_needed">Number of Members Needed:</label>
								<input type="number" id="edit_members_needed" name="members_needed" min="1" required><br>

								<button type="submit">Update</button>
							</form>
						</div>
					</div>
				</div>

				<!-- Discussion Section (Initially Hidden) -->
				<div id="discussion-content" class="main-content" style="display: none;">
					<h1>Discussion</h1><br>

					<!-- Styled as a Button -->
					<a href="discussion.php" id="newGABtn" style="display: inline-block; padding: 10px 20px; background-color: #007BFF; color: white; text-decoration: none; border-radius: 5px;">Go to Discussion</a>
				</div>

				<!-- Group Section (Initially Hidden) -->
				<div id="group-content" class="main-content" style="display: none;">
					<h1>Group Work</h1>

					<?php
					// Fetch group projects the user is involved in
					$leader_id = $_SESSION['student_id']; // Adjust based on your session variable
					$query = "
						SELECT * 
						FROM group_project 
						WHERE leader_id = ? 
						OR group_id IN (
							SELECT group_id FROM group_members WHERE student_id = ?
						)
					";
					$stmt = $connect->prepare($query);
					$stmt->bind_param("ii", $leader_id, $leader_id);
					$stmt->execute();
					$result = $stmt->get_result();

					if ($result->num_rows > 0): ?>
						<?php while ($group = $result->fetch_assoc()): ?>
							<div class="project-section">
								<br><hr><br><h2><?= htmlspecialchars($group['project_name']); ?></h2><br>
								<hr><br>
								<p><strong>Description:</strong> <?= htmlspecialchars($group['project_desc']); ?></p><br>
								<p><strong>Due Date:</strong> <?= htmlspecialchars($group['due_date']); ?></p><br>
								
								<p><strong>Progress:</strong></p><br>
								<div class="progress">
									<div class="progress-bar" id="progress-bar-<?= $group['group_id']; ?>" role="progressbar" 
										style="width: <?= $group['progress']; ?>%;" 
										aria-valuenow="<?= $group['progress']; ?>" 
										aria-valuemin="0" 
										aria-valuemax="100">
										<?= $group['progress']; ?>%
									</div>
								</div><br>

								<!-- Range Slider -->
								<input type="range" class="form-range" id="progress-slider-<?= $group['group_id']; ?>" 
									value="<?= $group['progress']; ?>" 
									min="0" 
									max="100" 
									oninput="updateProgress(<?= $group['group_id']; ?>, this.value)" 
									onchange="saveProgress(<?= $group['group_id']; ?>, this.value)">


								<!-- Group Members -->
								<br><?php include 'fetch_group_members.php'; ?>

								<!-- Announcements -->
								<br><h3>Announcements</h3>
								<form method="POST" action="update_announcement.php">
									<label for="announcement"><strong>Write Your Announcement:</strong></label>
									<textarea id="announcement" name="announcement" rows="6" class="form-control large-input"><?= htmlspecialchars($group['latest_announcement'] ?? ''); ?></textarea>
									<br><input type="hidden" name="group_id" value="<?= $group['group_id']; ?>">
									<button type="submit" class="btn btn-primary large-button">Update Announcement</button><br>
								</form>
							</div>
						<?php endwhile; ?>
					<?php else: ?>
						<p>No group projects available.</p>
					<?php endif; ?>
				</div>
				
				<!-- Setting Section (Initially Hidden) --> 
				<div id="setting-content" class="main-content" style="display: none;">
					<h1>Settings</h1>

					<!-- Profile Information Update Section -->
					<section>
						<h2>Profile Information</h2>
						<form id="profile-form" action="update_profile.php" method="POST">
							<label for="name">Name:</label><br>
							<input type="text" id="name" name="name" placeholder="Enter your name" 
								value="<?php echo isset($student['student_name']) ? htmlspecialchars($student['student_name']) : ''; ?>"><br><br>

							<label for="email">Email:</label><br>
							<input type="email" id="email" name="email" placeholder="Enter your email" 
								value="<?php echo isset($student['student_email']) ? htmlspecialchars($student['student_email']) : ''; ?>"><br><br>

							<label for="semester">Semester:</label><br>
							<input type="text" id="semester" name="semester" placeholder="Enter your semester" 
								value="<?php echo isset($student['student_semester']) ? htmlspecialchars($student['student_semester']) : ''; ?>"><br><br>

							<label for="current_password">Current Password:</label><br>
							<input type="password" id="current_password" name="current_password" placeholder="Enter current password" required><br><br>

							<label>Do you want to change your password?</label><br>
							<input type="radio" id="change_password_yes" name="change_password" value="yes">
							<label for="change_password_yes">Yes</label>
							<input type="radio" id="change_password_no" name="change_password" value="no" checked>
							<label for="change_password_no">No</label><br><br>

							<div id="new-password-section" style="display: none;">
								<label for="password">New Password:</label><br>
								<input type="password" id="password" name="password" placeholder="Enter new password"><br><br>
							</div>

							<button type="submit">Save Changes</button>
						</form>
					</section>
				</div>

				<!-- Lecturer Section (Initially Hidden) -->
				<div id="lecturer-content" class="main-content" style="display: none;">
					<h1>Lecturer Lists</h1><br>

					<?php if ($result_lecturers->num_rows > 0): ?>
						<table class="styled-table">
							<thead>
								<tr>
									<th>Name</th>
									<th>Email</th>
									<th>Phone</th>
								</tr>
							</thead>
							<tbody>
								<?php while ($row = $result_lecturers->fetch_assoc()): ?>
									<tr>
										<td><?= htmlspecialchars($row['name']); ?></td>
										<td><?= htmlspecialchars($row['email']); ?></td>
										<td><?= htmlspecialchars($row['phone']); ?></td>
									</tr>
								<?php endwhile; ?>
							</tbody>
						</table>
					<?php else: ?>
						<p>No lecturers available.</p>
					<?php endif; ?>
				</div>

				<!-- Logout Section (Initially Hidden) -->
				<div id="logout-content" class="main-content" style="display: none;">
					<h1>Leaving?</h1>
					<p>Are you sure to logout? Have you finished all your work?</p>

					<button id="logout-button" type="button">Yes, All done!</button>
				</div>
			</main>
			<!-- MAIN -->
		</section>
		<!-- CONTENT -->
		
		<script src="DBscript.js"></script>

		<script src="main2.js"></script>

		<script>// Show/hide new password field based on radio button selection
			document.addEventListener('DOMContentLoaded', function () {
				const changePasswordYes = document.getElementById('change_password_yes');
				const changePasswordNo = document.getElementById('change_password_no');
				const newPasswordSection = document.getElementById('new-password-section');

				function togglePasswordSection() {
					if (changePasswordYes.checked) {
						newPasswordSection.style.display = 'block';
					} else {
						newPasswordSection.style.display = 'none';
					}
				}

				changePasswordYes.addEventListener('change', togglePasswordSection);
				changePasswordNo.addEventListener('change', togglePasswordSection);
			});
		</script>
		
		<script>
			let sortProjectDirection = 'asc'; // Default sorting direction

			function toggleProjectFilter() {
				const tableBody = document.getElementById('projects-table-body');
				const rows = Array.from(tableBody.getElementsByTagName('tr'));

				// Toggle sorting direction
				sortProjectDirection = (sortProjectDirection === 'asc') ? 'desc' : 'asc';

				// Sort rows based on project name
				rows.sort((a, b) => {
					const nameA = a.getAttribute('data-project-name').toLowerCase();
					const nameB = b.getAttribute('data-project-name').toLowerCase();

					if (sortProjectDirection === 'asc') {
						return nameA.localeCompare(nameB);
					} else {
						return nameB.localeCompare(nameA);
					}
				});

				// Re-append sorted rows to the table body
				tableBody.innerHTML = '';
				rows.forEach(row => tableBody.appendChild(row));

				// Update filter icon to indicate current sorting direction
				const filterIcon = document.getElementById('filter-icon');
				filterIcon.classList.toggle('bx-sort-down', sortProjectDirection === 'asc');
				filterIcon.classList.toggle('bx-sort-up', sortProjectDirection === 'desc');
			}
		</script>

		<script>
			let sortDirection = 'asc'; // Default sorting direction

			function toggleFilter() {
				const list = document.getElementById('todo-list');
				const items = Array.from(list.getElementsByTagName('li'));

				// Toggle sorting direction
				sortDirection = (sortDirection === 'asc') ? 'desc' : 'asc';

				// Sort items based on task name (ascending or descending)
				items.sort((a, b) => {
					const nameA = a.getAttribute('data-task-name').toLowerCase();
					const nameB = b.getAttribute('data-task-name').toLowerCase();

					if (sortDirection === 'asc') {
						return nameA.localeCompare(nameB);
					} else {
						return nameB.localeCompare(nameA);
					}
				});

				// Re-append sorted items to the list
				list.innerHTML = '';
				items.forEach(item => list.appendChild(item));

				// Update the filter icon to indicate current sorting direction
				const filterIcon = document.getElementById('filter-icon');
				filterIcon.classList.toggle('bx-sort-down', sortDirection === 'asc');
				filterIcon.classList.toggle('bx-sort-up', sortDirection === 'desc');
			}
		</script>

		<script>
			function updateProgress(groupId, value) {
				// Update the progress bar width and text dynamically
				const progressBar = document.getElementById(`progress-bar-${groupId}`);
				progressBar.style.width = `${value}%`;
				progressBar.setAttribute('aria-valuenow', value);
				progressBar.textContent = `${value}%`;
			}

			function saveProgress(groupId, value) {
				// Send the updated progress to the server using Fetch API
				fetch('update_progress.php', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
					},
					body: JSON.stringify({
						group_id: groupId,
						progress: value,
					}),
				})
				.then(response => response.json())
				.then(data => {
					if (data.success) {
						alert("Progress updated successfully!");
					} else {
						alert("Failed to update progress.");
					}
				})
				.catch(error => {
					console.error("Error updating progress:", error);
					alert("An error occurred. Please try again.");
				});
			}
		</script>

		<script>
			// Handle search form submission
			document.getElementById('search-form').addEventListener('submit', function(e) {
				e.preventDefault();  // Prevent the form from submitting normally

				// Get the search query
				const query = document.getElementById('search-query').value;

				// Use Fetch API to send the search query to the server
				fetch('search_student.php', {
					method: 'POST',
					body: new URLSearchParams({
						query: query
					})
				})
				.then(response => response.json()) // Parse JSON response
				.then(data => {
					// Display the search results in the modal
					const resultsContainer = document.getElementById('search-results');
					resultsContainer.innerHTML = ''; // Clear previous results

					if (data.length === 0) {
						resultsContainer.innerHTML = '<p>No results found.</p>';
					} else {
						// Loop through the results and display them
						data.forEach(item => {
							const resultItem = document.createElement('div');
							resultItem.classList.add('search-result');
							resultItem.innerHTML = `
								<h4>${item.name}</h4>
								<p>${item.description}</p>
							`;
							resultsContainer.appendChild(resultItem);
						});
					}

					// Open the modal
					document.getElementById('search-modal').style.display = 'block';
				})
				.catch(error => {
					console.error('Error:', error);
				});
			});

			// Close the modal when the close button is clicked
			document.getElementById('close-modal').addEventListener('click', function() {
				document.getElementById('search-modal').style.display = 'none';
			});
		</script>

	</body>
</html>