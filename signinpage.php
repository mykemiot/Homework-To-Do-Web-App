<?php
    // Start session at the very top
    session_start();

    // Include the database connection file
    include("connection.php");

    // Initialize an array to store errors
    $errors = [];

    // Handle form submission for student sign-up
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['studentSignup'])) {
        // Retrieve and validate form data
        if (empty($_POST['student_name'])) {
            $errors[] = 'You forgot to enter your name.';
        } else {
            $studentName = mysqli_real_escape_string($connect, trim($_POST['student_name']));
        }

        if (empty($_POST['student_email'])) {
            $errors[] = 'You forgot to enter your email.';
        } else {
            $studentEmail = mysqli_real_escape_string($connect, trim($_POST['student_email']));
        }

        if (empty($_POST['student_password'])) {
            $errors[] = 'You forgot to enter the password.';
        } else {
            $studentPassword = mysqli_real_escape_string($connect, trim($_POST['student_password']));
        }

        if (empty($_POST['student_semester'])) {
            $errors[] = 'You forgot to enter your semester.';
        } else {
            $studentSemester = mysqli_real_escape_string($connect, trim($_POST['student_semester']));
        }

        // If there are no errors, insert the student into the database
        if (empty($errors)) {
            $sql = "INSERT INTO student (student_name, student_email, student_password, student_semester) 
                    VALUES ('$studentName', '$studentEmail', '$studentPassword', '$studentSemester')";

            if (mysqli_query($connect, $sql)) {
                echo "<script>alert('Account successfully created!');</script>";
            } else {
                echo "<script>alert('System error: " . mysqli_error($connect) . "');</script>";
            }
        }
    }

    // Handle form submission for student sign-in
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['studentSignin'])) {
        if (empty($_POST['student_email'])) {
            $errors[] = 'You forgot to enter your email.';
        } else {
            $studentEmail = mysqli_real_escape_string($connect, trim($_POST['student_email']));
        }

        if (empty($_POST['student_password'])) {
            $errors[] = 'You forgot to enter your password.';
        } else {
            $studentPassword = mysqli_real_escape_string($connect, trim($_POST['student_password']));
        }

        // If no errors, proceed with sign-in
        if (empty($errors)) {
            $sql = "SELECT student_id, student_password FROM student WHERE student_email = '$studentEmail'";
            $result = mysqli_query($connect, $sql);

            if (mysqli_num_rows($result) == 1) {
                $row = mysqli_fetch_assoc($result);

                // Direct comparison without password hashing
                if ($studentPassword == $row['student_password']) {
                    // Start session and redirect to student dashboard
                    $_SESSION['student_id'] = $row['student_id'];
                    header("Location: dashboardpage.php");
                    exit();
                } else {
                    $errors[] = "<script>alert('Incorrect username and password.')</script>";
                }
            } else {
                $errors[] = "<script>alert('The account does not exist.')</script>";
            }
        }
    }

    // Handle form submission for student sign-in
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['studentSignin'])) {
        if (empty($_POST['student_email'])) {
            $errors[] = 'You forgot to enter your email.';
        } else {
            $studentEmail = mysqli_real_escape_string($connect, trim($_POST['student_email']));
        }

        if (empty($_POST['student_password'])) {
            $errors[] = 'You forgot to enter your password.';
        } else {
            $studentPassword = mysqli_real_escape_string($connect, trim($_POST['student_password']));
        }

        // If no errors, proceed with sign-in
        if (empty($errors)) {
            $sql = "SELECT student_id, student_password FROM student WHERE student_email = '$studentEmail'";
            $result = mysqli_query($connect, $sql);

            if (mysqli_num_rows($result) == 1) {
                $row = mysqli_fetch_assoc($result);

                // Verify the password using password_verify
                if (password_verify($studentPassword, $row['student_password'])) {
                    // Start session and redirect to student dashboard
                    session_start();
                    $_SESSION['student_id'] = $row['student_id'];
                    header("Location: dashboardpage.php");
                    exit();
                } else {
                    $errors[] = "<script>alert('Incorrect email and password.')</script>";
                }
            } else {
                $errors[] = "<script>alert('The account does not exist.')</script>";
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="SIstyleTEST.css">
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">

    <title>Sign Up Today!</title>
</head>

<body>
    <?php
        if (!empty($errors)) {
            foreach ($errors as $error) {
                echo "<p>$error</p>";
            }
        }
    ?>

    <div class="container" id="container">
        <div class="form-container sign-up">
            <form method="POST" action="">
                <h1>Create Account</h1>
                <span>Use your email for registration</span>
                <input type="text" name="student_name" placeholder="Name" required>
                <input type="email" name="student_email" placeholder="Email" required>
                <input type="password" name="student_password" placeholder="Password" required>
                <input type="text" name="student_semester" placeholder="Semester" required>
                <button type="submit" name="studentSignup">Sign Up</button> <!-- Ensure the name attribute is set -->
            </form>
        </div>

        <!-- Sign-In Form -->
        <div class="form-container sign-in">
            <form method="POST" action="">
                <h1>Sign In</h1>
                <input type="email" name="student_email" placeholder="Email" required>
                <input type="password" name="student_password" placeholder="Password" required>
                <button type="submit" name="studentSignin">Sign In</button>
            </form>
        </div>

        <div class="toggle-container">
            <div class="toggle">
                <div class="toggle-panel toggle-left">
                    <h1>Welcome Back!</h1>
                    <p>Enter your sign in to continue where you left</p>
                    <button class="hidden" id="login">Sign In</button>
                </div>
                <div class="toggle-panel toggle-right">
                    <h1>Hello, My Mate! New to Homework To-Do?</h1>
                    <p>Register with your personal details to use all of site features</p>
                    <button class="hidden" id="register">Sign Up</button>
                </div>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>

</html>
