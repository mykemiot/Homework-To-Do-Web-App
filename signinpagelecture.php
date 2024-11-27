<?php
    // Start session at the very top
    session_start();

    // Include the database connection file
    include("connection.php");

    // Initialize an array to store errors
    $errors = [];

    // Handle form submission for student sign-up
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['Signup'])) {
        // Retrieve and validate form data
        if (empty($_POST['name'])) {
            $errors[] = 'You forgot to enter your name.';
        } else {
            $name = mysqli_real_escape_string($connect, trim($_POST['name']));
        }

        if (empty($_POST['email'])) {
            $errors[] = 'You forgot to enter your email.';
        } else {
            $email = mysqli_real_escape_string($connect, trim($_POST['email']));
        }

        if (empty($_POST['password'])) {
            $errors[] = 'You forgot to enter the password.';
        } else {
            $password = mysqli_real_escape_string($connect, trim($_POST['password']));
        }

        if (empty($_POST['phone'])) {
            $errors[] = 'You forgot to enter your phone number.';
        } else {
            $phone = mysqli_real_escape_string($connect, trim($_POST['phone']));
        }

        // If there are no errors, insert the student into the database
        if (empty($errors)) {
            $sql = "INSERT INTO lecture (name, email, password, phone) 
                    VALUES ('$name', '$email', '$password', '$phone')";

            if (mysqli_query($connect, $sql)) {
                echo "<script>alert('Account successfully created!');</script>";
            } else {
                echo "<script>alert('System error: " . mysqli_error($connect) . "');</script>";
            }
        }
    }

    // Handle form submission for student sign-in
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['Signin'])) {
        if (empty($_POST['email'])) {
            $errors[] = 'You forgot to enter your email.';
        } else {
            $email = mysqli_real_escape_string($connect, trim($_POST['email']));
        }

        if (empty($_POST['password'])) {
            $errors[] = 'You forgot to enter your password.';
        } else {
            $password = mysqli_real_escape_string($connect, trim($_POST['password']));
        }

        // If no errors, proceed with sign-in
        if (empty($errors)) {
            $sql = "SELECT id, password FROM lecture WHERE email = '$email'";
            $result = mysqli_query($connect, $sql);

            if (mysqli_num_rows($result) == 1) {
                $row = mysqli_fetch_assoc($result);

                // Direct comparison without password hashing
                if ($password == $row['password']) {
                    // Start session and redirect to student dashboard
                    $_SESSION['id'] = $row['id'];
                    header("Location: dashboardpagelecture.php");
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
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['Signin'])) {
        if (empty($_POST['email'])) {
            $errors[] = 'You forgot to enter your email.';
        } else {
            $email = mysqli_real_escape_string($connect, trim($_POST['email']));
        }

        if (empty($_POST['password'])) {
            $errors[] = 'You forgot to enter your password.';
        } else {
            $password = mysqli_real_escape_string($connect, trim($_POST['password']));
        }

        // If no errors, proceed with sign-in
        if (empty($errors)) {
            $sql = "SELECT id, password FROM lecture WHERE email = '$email'";
            $result = mysqli_query($connect, $sql);

            if (mysqli_num_rows($result) == 1) {
                $row = mysqli_fetch_assoc($result);

                // Verify the password using password_verify
                if (password_verify($password, $row['password'])) {
                    // Start session and redirect to student dashboard
                    session_start();
                    $_SESSION['id'] = $row['id'];
                    header("Location: dashboardpagelecture.php");
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
                <input type="text" name="name" placeholder="Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="text" name="phone" placeholder="Phone" required>
                <button type="submit" name="Signup">Sign Up</button>
            </form>
        </div>

        <!-- Sign-In Form -->
        <div class="form-container sign-in">
            <form method="POST" action="">
                <h1>Sign In</h1>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="Signin">Sign In</button>
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
                    <h1>Hello, Lecture! New to Homework To-Do?</h1>
                    <p>Register with your personal details to monitor student's tasks and assignments!</p>
                    <button class="hidden" id="register">Sign Up</button>
                </div>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>

</html>
