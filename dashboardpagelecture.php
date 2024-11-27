<?php
session_start();
include("connection.php");

// Check if the user is logged in (lecture)
if (!isset($_SESSION['id'])) {
    // Redirect to login page if the user is not logged in
    header('Location: signinpagelecture.php');
    exit;
}

// Assuming the lecture ID is stored in the session after login
$lecture_id = $_SESSION['id'];

// Retrieve the lecture's information based on the lecture_id
$sql = "SELECT * FROM lecture WHERE id = ?";
$stmt = $connect->prepare($sql);
$stmt->bind_param("i", $lecture_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if the lecture exists
if ($result->num_rows > 0) {
    $lecture = $result->fetch_assoc();
} else {
    // Handle case where lecture is not found
    echo "Lecture not found.";
    exit;
}

// SQL query to count total students
$sql_students = "SELECT COUNT(*) AS total_students FROM student";
$result_students = $connect->query($sql_students);
if ($result_students->num_rows > 0) {
    $row_students = $result_students->fetch_assoc();
    $totalStudents = $row_students['total_students'];
} else {
    $totalStudents = 0;
}

// SQL query to count total projects
$sql_projects = "SELECT COUNT(*) AS total_projects FROM project"; 
$result_projects = $connect->query($sql_projects);
if ($result_projects->num_rows > 0) {
    $row_projects = $result_projects->fetch_assoc();
    $totalProjects = $row_projects['total_projects'];
} else {
    $totalProjects = 0;
}

// Retrieve all projects from the projects table
$sql_projects = "SELECT * FROM project";
$result_projects = $connect->query($sql_projects);

// Retrieve all tasks
$sql_tasks = "SELECT * FROM task";
$result_tasks = $connect->query($sql_tasks);

// Retrieve all projects (for project tab)
$sql_projects2 = "SELECT * FROM project";
$result_projects2 = $connect->query($sql_projects2);

// Retrieve all tasks (for task tab)
$sql_tasks2 = "
    SELECT t.*, p.project_name
    FROM task t
    LEFT JOIN project p ON t.project_id = p.project_id
";
$result_tasks2 = $connect->query($sql_tasks2);

// Retrieve all group projects 
$sql_group_projects = "
    SELECT gp.*, s.student_name AS leader_name
    FROM group_project gp
    JOIN student s ON gp.leader_id = s.student_id";
$result_group_projects = $connect->query($sql_group_projects);

// Retrieve all students' information
$sql_students = "SELECT student_id, student_name, student_email, student_semester FROM student";
$result_students = $connect->query($sql_students);

$stmt->close();
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
					<i class='bx bxs-group'></i>
					<span class="text">Project</span>
				</a>
			</li>
			<li>
				<a href="#" data-section="task-content">
					<i class='bx bxs-message-dots'></i>
					<span class="text">Task</span>
				</a>
			</li>
			<li>
				<a href="#" data-section="groupasg-content">
					<i class='bx bxs-message-dots'></i>
					<span class="text">Group Assignment</span>
				</a>
			</li>
            <li>
				<a href="#" data-section="student-content">
					<i class='bx bxs-group'></i>
					<span class="text">Student</span>
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
        </nav>

        <!-- Modal for Search Results -->
        <div id="search-modal" class="modal">
            <div class="modal-content">
                <span id="close-modal" class="close">&times;</span>
                <h3>Search Results</h3>
                <div id="search-results"></div>
            </div>
        </div>

		<!-- MAIN -->
		<main>
			<!-- Dashboard Section -->
			<div id="dashboard-content" class="main-content" style="display: none;">
				<div class="head-title">
					<div class="left">
						<h2>Dashboard</h2><br>
						<p>Welcome Back! <?php echo htmlspecialchars($lecture['name']); ?></p>
					</div>
				</div>

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
				
				<!-- Display project and task information -->
				<div class="table-data">
					<div class="order">
						<div class="head">
							<h3>Projects</h3>
						</div>
						<table>
							<thead>
								<tr>
									<th>Project Title</th>
									<th>Due Date</th>
									<th>Status</th>
								</tr>
							</thead>
							<tbody>
                                <?php
                                    // Check if there are any projects
                                    if ($result_projects->num_rows > 0) {
                                        // Loop through each project and display its data
                                        while ($row = $result_projects->fetch_assoc()) {
                                            echo "<tr>";
                                            echo "<td>" . htmlspecialchars($row['project_name']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['project_duedate']) . "</td>";
                                            $statusClass = ($row['project_status'] == 'Completed') ? 'completed' : 'pending';
                                            echo "<td><span class='status $statusClass'>" . htmlspecialchars($row['project_status']) . "</span></td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        // If no projects are found, display a message
                                        echo "<tr><td colspan='3'>No projects found</td></tr>";
                                    }
                                ?>
							</tbody>
						</table>
					</div>

					<div class="todo">
						<div class="head">
							<h3>Tasks</h3>
							<i class='bx bx-plus' ></i>
							<i class='bx bx-filter' ></i>
						</div>
						<ul class="todo-list">
                            <?php
                                // Check if tasks exist
                                if ($result_tasks->num_rows > 0) {
                                    // Loop through tasks and display them
                                    while ($task = $result_tasks->fetch_assoc()) {
                                        // Set class based on task status
                                        $taskClass = ($task['task_status'] == 'Completed') ? 'completed' : 'not-completed';
                                        
                                        echo "<li class='$taskClass'>";
                                        echo "<p>" . htmlspecialchars($task['task_name']) . "</p>";
                                        echo "<i class='bx bx-dots-vertical-rounded'></i>";
                                        echo "</li>";
                                    }
                                } else {
                                    echo "<li>No tasks available.</li>";
                                }
                            ?>
						</ul>
					</div>
				</div>
			</div>
		
			<!-- Project Section (Initially Hidden) -->
			<div id="project-content" class="main-content" style="display: none;">
				<h2>Project Details</h2><br>
		
				<!-- Card Container -->
                <div class="card-columns">
                    <?php if ($result_projects2->num_rows > 0): ?>
                        <?php while ($row = $result_projects2->fetch_assoc()): ?>
                            <div class="card">
                                <div class="card-body">
                                    <!-- Project Name -->
                                    <h5 class="card-title"><?= htmlspecialchars($row['project_name']); ?></h5><br>
                                    
                                    <!-- Project Description -->
                                    <p class="card-text"><?= htmlspecialchars($row['project_desc']); ?></p><br>
                                    
                                    <!-- Project Due Date -->
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
                                    </p>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>No projects available.</p>
                    <?php endif; ?>
                </div>
			</div>

			<!-- Task Section (Initially Hidden) -->
			<div id="task-content" class="main-content" style="display: none;">
				<h1>Tasks</h1><br>

				<!-- Card Container for Tasks -->
                <div class="card-columns">
                    <?php if ($result_tasks2->num_rows > 0): ?>
                        <?php while ($row = $result_tasks2->fetch_assoc()): ?>
                            <div class="card">
                                <div class="card-body">
                                    <!-- Task Name -->
                                    <h5 class="card-title"><?= htmlspecialchars($row['task_name']); ?></h5><br>

                                    <!-- Task Description -->
                                    <p class="card-text"><?= htmlspecialchars($row['task_desc']); ?></p><br>

                                    <!-- Task Due Date -->
                                    <p class="card-text"><small class="text-muted">Due: <?= htmlspecialchars($row['task_duedate']); ?></small></p><br>

                                    <!-- Project Name -->
                                    <p class="card-text"><strong>Project:</strong> <?= htmlspecialchars($row['project_name']); ?></p><br>

                                    <!-- Task Status Badge -->
                                    <p class="card-text">
                                        <span class="badge badge-<?= $row['task_status'] == 'Completed' ? 'success' : ($row['task_status'] == 'Ongoing' ? 'warning' : 'secondary'); ?>">
                                            <?= htmlspecialchars($row['task_status']); ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>No tasks available.</p>
                    <?php endif; ?>
                </div>
			</div>

			<!-- Group Assignment Section (Initially Hidden) -->
			<div id="groupasg-content" class="main-content" style="display: none;">
				<h1>Group Assignments</h1><br>
				
				<!-- Group Project Card Container -->
                <div class="container">
                    <?php if ($result_group_projects->num_rows > 0): ?>
                        <?php while ($row = $result_group_projects->fetch_assoc()): ?>
                            <div class="card">
                                <div class="card-body">
                                    <!-- Project Name -->
                                    <h5 class="card-title"><?= htmlspecialchars($row['project_name']); ?></h5><br>

                                    <!-- Project Leader Name -->
                                    <p class="card-text"><strong>Leader:</strong> <?= htmlspecialchars($row['leader_name']); ?></p><br>

                                    <!-- Members Needed -->
                                    <p class="card-text"><strong>Members Needed:</strong> <?= htmlspecialchars($row['members_needed']); ?></p><br>

                                    <!-- Project Description -->
                                    <p class="card-text"><?= htmlspecialchars($row['project_desc']); ?></p><br>

                                    <!-- Due Date -->
                                    <p class="card-text"><small class="text-muted">Due: <?= htmlspecialchars($row['due_date']); ?></small></p><br>

                                    <!-- Status Badge -->
                                    <p class="card-text">
                                        <span class="badge badge-<?= $row['status'] == 'Completed' ? 'success' : ($row['status'] == 'Ongoing' ? 'warning' : 'secondary'); ?>">
                                            <?= htmlspecialchars($row['status']); ?>
                                        </span>
                                    </p><br>

                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>No group projects available.</p>
                    <?php endif; ?>
                </div>
			</div>

            <!-- Discussion Section (Initially Hidden) -->
            <div id="discussion-content" class="main-content" style="display: none;">
                <h1>Discussion</h1><br>

                <!-- Styled as a Button -->
                <a href="discussion_lecture_view.php" id="newGABtn" style="display: inline-block; padding: 10px 20px; background-color: #007BFF; color: white; text-decoration: none; border-radius: 5px;">Go to Discussion</a>
            </div>

			<!-- Student Section (Initially Hidden) -->
            <div id="student-content" class="main-content" style="display: none;">
                <h1>Student Lists</h1><br>

                <?php if ($result_students->num_rows > 0): ?>
                    <table class="styled-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Semester</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result_students->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['student_name']); ?></td>
                                    <td><?= htmlspecialchars($row['student_email']); ?></td>
                                    <td><?= htmlspecialchars($row['student_semester']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No students available.</p>
                <?php endif; ?>
            </div>

			<!-- Logout Section (Initially Hidden) -->
			<div id="logout-content" class="main-content" style="display: none;">
				<h1>Leaving?</h1>
				<p>Are you sure to logout?</p>

				<button id="logout-button" type="button">Yes</button>
			</div>
		</div>
		</main>
		<!-- MAIN -->
	</section>
	<!-- CONTENT -->
	

	<script src="DBscripts.js"></script>

    <!-- Search -->
    <script>
        // Handle search form submission
        document.getElementById('search-form').addEventListener('submit', function(e) {
            e.preventDefault();  // Prevent the form from submitting normally

            // Get the search query
            const query = document.getElementById('search-query').value;

            // Use Fetch API to send the search query to the server
            fetch('search.php', {
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