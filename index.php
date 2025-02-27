<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Project Management System</title>
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
    <header style="background-color: #023B87;">
        <div class="container">
            <h1>Project Management System</h1>
            <nav>
                <ul>
                    <li><a href="#about">About</a></li>
                    <li><a href="login.php" class="btn-login">Login</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <section id="hero">
        <div class="carousel">
            <div class="carousel-image active">
                <img src="img1.jpg" style="">
            </div>
            <div class="carousel-image">
                <img src="img2.jpg" style="">
            </div>
            <div class="carousel-image">
            <img src="img3.jpg" style="">
            </div>
            <button class="carousel-button prev" onclick="changeSlide(-1)">&#10094;</button>
            <button class="carousel-button next" onclick="changeSlide(1)">&#10095;</button>
        </div>
        <div class="container hero-content" style="color: black;">
            <h2 style="text-shadow: 2px 2px 10px white;">Organize and Streamline Your Projects</h2>
            <p style="text-shadow: 2px 2px 10px white;">Effortlessly manage tasks, deadlines, and team communication with our powerful Project Management System.</p>
        </div>
    </section>

    <section id="about">
        <div class="container">
            <h2>About Us</h2>
            <p>Our Project Management System is designed to help teams of all sizes manage their projects efficiently. From task tracking to collaboration tools, we provide everything you need to keep your projects on track.</p>
        </div>
    </section>

    <section id="features">
        <div class="container">
            <h2>Key Features</h2>
            <div class="feature-box">
                <h3>Task Assignment</h3>
                <p>Easily assign tasks and monitor progress across all your projects.</p>
            </div>
            <div class="feature-box">
                <h3>Communication</h3>
                <p>Communicate with your team and stay updated on project status in real time.</p>
            </div>
            <div class="feature-box">
                <h3>Progress Tracking</h3>
                <p>Track deadlines, milestones, and overall project performance through detailed analytics.</p>
            </div>
        </div>
    </section>

    <section id="contact">
        <div class="container">
            <h2>Contact Us</h2>
            <p>Have questions or need help? Contact our support team at <a href="hccpms1946@gmail.com">hccpms1946@gmail.com</a></p>
        </div>
    </section>

    <footer>
        <div class="container">
            <p>&copy; 2024 Project Management System. All Rights Reserved.</p>
        </div>
    </footer>

    <script src="./js/script.js"></script>
</body>
</html>
