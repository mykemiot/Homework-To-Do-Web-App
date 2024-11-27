<?php
// Start the session at the very beginning of the file
session_start();
include("connection.php");
?>

<html>
<head>
    <title>Homework To-Do</title>
    <link rel="stylesheet" href="LPstyle.css">
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
</head>
<body>
    <div class="banner">
        <div class="navbar">
            <img src="img/logo.png" class="logo">
            <ul>
                <li><a href="landingpage.php">Home</a></li>
                <li><a href="signinpagelecture.php">Are you a Lecturer?</a></li>
                <li><a href="docs/USER MANUAL.pdf" target="_blank" rel="noopener noreferrer">New Here?</a></li>
            </ul>
        </div>

        <div class="content">
            <h1>Welcome to Homework To-Do</h1>
            <p>Collaborate and Manage Projects with Ease</p>

            <div>
                <a href="signinpage.php"><button type="button"><span></span>ENTER NOW</button></a>
            </div>
        </div>
    </div>

    <!-- New section for the slideshow -->
    <section class="slideshow-section">
        <div class="slideshow-container">
            <!-- Slide 1 -->
            <div class="mySlides fade">
                <img src="img/3.png" style="width:100%">
            </div>

            <!-- Slide 2 -->
            <div class="mySlides fade">
                <img src="img/logo.png" style="width:100%">
            </div>

            <!-- Slide 3 -->
            <div class="mySlides fade">
                <img src="img/3.png" style="width:100%">
            </div>

            <!-- Next and previous buttons -->
            <a class="prev" onclick="plusSlides(-1)"><i class="arrow left"></i></a>
            <a class="next" onclick="plusSlides(1)"><i class="arrow right"></i></a>
        </div>

        <!-- Dots (for navigation) -->
        <div style="text-align:center">
            <span class="dot" onclick="currentSlide(1)"></span>
            <span class="dot" onclick="currentSlide(2)"></span>
            <span class="dot" onclick="currentSlide(3)"></span>
        </div>
    </section>

    <script src="slideshow.js"></script>
</body>
</html>
