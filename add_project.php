<?php
session_start();
include 'connection.php'; // Database connection

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $project_name = trim($_POST['project_name']);
    $project_desc = trim($_POST['project_desc']);
    $project_duedate = $_POST['project_duedate'];
    $project_status = $_POST['project_status'];
    $uploaded_by = $_SESSION['student_id']; // Assuming the student ID is in session

    // Insert into project table with student_id
    $query = "INSERT INTO project (project_name, project_desc, project_duedate, project_status, student_id) VALUES (?, ?, ?, ?, ?)";
    $stmt = $connect->prepare($query);
    $stmt->bind_param("ssssi", $project_name, $project_desc, $project_duedate, $project_status, $uploaded_by);

    if ($stmt->execute()) {
        $project_id = $connect->insert_id; // Get the last inserted project_id

        // Check if a file was uploaded
        if (isset($_FILES['project_file']) && $_FILES['project_file']['error'] == 0) {
            $file_name = $_FILES['project_file']['name'];
            $file_tmp = $_FILES['project_file']['tmp_name'];
            $upload_date = date('Y-m-d H:i:s');

            // Check file type (example: only allow PDFs and DOCX)
            $allowed_types = ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
            $file_type = mime_content_type($file_tmp);

            if (in_array($file_type, $allowed_types)) {
                // Sanitize file name
                $file_name = basename($file_name);
                
                // Define file upload path
                $file_path = "uploads/" . $file_name;

                // Ensure the upload directory exists
                if (!is_dir("uploads")) {
                    mkdir("uploads", 0777, true);
                }

                // Check if the file already exists
                if (!file_exists($file_path)) {
                    // Move the uploaded file to the server
                    if (move_uploaded_file($file_tmp, $file_path)) {
                        // Insert into file table
                        $file_query = "INSERT INTO file (project_id, uploaded_by, file_name, file_path, upload_date) VALUES (?, ?, ?, ?, ?)";
                        $file_stmt = $connect->prepare($file_query);
                        $file_stmt->bind_param("iisss", $project_id, $uploaded_by, $file_name, $file_path, $upload_date);
                        if ($file_stmt->execute()) {
                            echo "<script>
                                    alert('Project added successfully with file!');
                                    window.location.href = 'dashboardpage.php';
                                  </script>";
                        } else {
                            echo "<script>
                                    alert('Error uploading file: " . $file_stmt->error . "'');
                                    window.location.href = 'dashboardpage.php';
                                  </script>";
                        }
                        $file_stmt->close();
                    } else {
                        echo "<script>
                                alert('Error moving the uploaded file.');
                                window.location.href = 'dashboardpage.php';
                              </script>";
                    }
                } else {
                    echo "<script>
                            alert('File already exists.');
                            window.location.href = 'dashboardpage.php';
                          </script>";
                }
            } else {
                echo "<script>
                        alert('Invalid file type. Only PDF and DOCX are allowed.');
                        window.location.href = 'dashboardpage.php';
                      </script>";
            }
        } else {
            // If no file is uploaded, still redirect
            echo "<script>
                    alert('Project added successfully without file!');
                    window.location.href = 'dashboardpage.php';
                  </script>";
        }

    } else {
        echo "<script>
                alert('Error adding project: " . $stmt->error . "');
                window.location.href = 'dashboardpage.php';
              </script>";
    }

    $stmt->close();
    $connect->close();
}
?>
