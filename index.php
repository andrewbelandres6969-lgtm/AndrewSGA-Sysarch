<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>CCS Sit-In Monitoring System</title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<nav class="navbar">
    <div class="logo-section">
        <img src="CCSlogo.png" class="logo" alt="Logo">
        <h2>College of Computer Studies Sit-In Monitoring System</h2>
    </div>

    <div class="nav-links">
        <a onclick="showHome()">Home</a>
        <a href="reports.php">Reports</a>

        <?php if (isset($_SESSION['student_id'])): ?>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="admin_dashboard.php">Admin Panel</a>
            <?php else: ?>
                <a href="student_dashboard.php">Dashboard</a>
            <?php endif; ?>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a onclick="showLogin()">Log In</a>
            <a class="register-btn" onclick="showRegister()">Register</a>
        <?php endif; ?>
    </div>
</nav>

<?php if (isset($_GET['success'])): ?>
    <div class="message success-msg"><?php echo htmlspecialchars($_GET['success']); ?></div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="message error-msg"><?php echo htmlspecialchars($_GET['error']); ?></div>
<?php endif; ?>

<section id="homeSection" class="hero">
    <h1>Welcome to the Future of Laboratory Monitoring</h1>
    <p>
        Empowering CCS students with a fast, secure, and intelligent Sit-In Monitoring System.
        Track attendance, monitor lab usage, and improve productivity with innovation.
    </p>
    <button class="btn-primary" onclick="showRegister()">Get Started</button>

    <p class="login-text">
        Already have an account?
        <span onclick="showLogin()">Log In</span>
    </p>
</section>

<section id="registerSection" class="hidden">
    <div class="register-container">
        <div class="register-image">
            <img src="registerGift.gif" alt="Register">
        </div>

        <div class="register-form">
            <span class="back-btn" onclick="showHome()">← Back</span>
            <h2>Sign Up</h2>

            <form action="register.php" method="POST">
                <input type="text" name="student_id" placeholder="ID Number" required>
                <input type="text" name="last_name" placeholder="Last Name" required>
                <input type="text" name="first_name" placeholder="First Name" required>
                <input type="text" name="middle_name" placeholder="Middle Name">
                <input type="text" name="course_level" placeholder="Course Level">
                <input type="password" name="password" placeholder="Password" required>
                <input type="password" name="repeat_password" placeholder="Repeat Password" required>
                <input type="email" name="email" placeholder="Email">
                <select name="course" required>
    <option value="">Select Course</option>

    <!-- IT / Computer Courses -->
    <option value="BS CS">BS CS (BACHELOR OF SCIENCE IN COMPUTER SCIENCE)</option>
    <option value="BS IT">BS IT (BACHELOR OF SCIENCE IN INFORMATION TECHNOLOGY)</option>
    <option value="BS CIS">BS CIS (BACHELOR OF SCIENCE IN COMPUTER INFORMATION SYSTEMS)</option>
    <option value="BSED InfoTech">BSED InfoTech (BACHELOR OF SECONDARY EDUCATION IN INFORMATION TECHNOLOGY)</option>

    <!-- Engineering -->
    <option value="BSCE">BSCE (BACHELOR OF SCIENCE IN CIVIL ENGINEERING)</option>
    <option value="BSEE">BSEE (BACHELOR OF SCIENCE IN ELECTRICAL ENGINEERING)</option>
    <option value="BSME">BSME (BACHELOR OF SCIENCE IN MECHANICAL ENGINEERING)</option>
    <option value="BSECE">BSECE (BACHELOR OF SCIENCE IN ELECTRONICS ENGINEERING)</option>

    <!-- Business -->
    <option value="BSBA">BSBA (BACHELOR OF SCIENCE IN BUSINESS ADMINISTRATION)</option>
    <option value="BSA">BSA (BACHELOR OF SCIENCE IN ACCOUNTANCY)</option>
    <option value="BSMA">BSMA (BACHELOR OF SCIENCE IN MANAGEMENT ACCOUNTING)</option>
    <option value="BSHM">BSHM (BACHELOR OF SCIENCE IN HOSPITALITY MANAGEMENT)</option>
    <option value="BSTM">BSTM (BACHELOR OF SCIENCE IN TOURISM MANAGEMENT)</option>

    <!-- Education -->
    <option value="BEED">BEED (BACHELOR OF ELEMENTARY EDUCATION)</option>
    <option value="BSED English">BSED English (BACHELOR OF SECONDARY EDUCATION MAJOR IN ENGLISH)</option>
    <option value="BSED Math">BSED Math (BACHELOR OF SECONDARY EDUCATION MAJOR IN MATHEMATICS)</option>

    <!-- Health / Science -->
    <option value="BSN">BSN (BACHELOR OF SCIENCE IN NURSING)</option>
    <option value="BSMLS">BSMLS (BACHELOR OF SCIENCE IN MEDICAL LABORATORY SCIENCE)</option>
    <option value="BSPH">BSPH (BACHELOR OF SCIENCE IN PUBLIC HEALTH)</option>
    <option value="BSBio">BSBio (BACHELOR OF SCIENCE IN BIOLOGY)</option>

    <!-- Arts & Social Sciences -->
    <option value="BA Comm">BA Comm (BACHELOR OF ARTS IN COMMUNICATION)</option>
    <option value="BA Psych">BA Psych (BACHELOR OF ARTS IN PSYCHOLOGY)</option>
    <option value="BS Psych">BS Psych (BACHELOR OF SCIENCE IN PSYCHOLOGY)</option>
    <option value="BA PolSci">BA PolSci (BACHELOR OF ARTS IN POLITICAL SCIENCE)</option>

    <!-- Criminology -->
    <option value="BS Crim">BS Crim (BACHELOR OF SCIENCE IN CRIMINOLOGY)</option>
</select>
                <input type="text" name="address" placeholder="Address">
                <button type="submit" class="btn-primary">Register</button>
            </form>
        </div>
    </div>
</section>

<section id="loginSection" class="hidden">
    <div class="register-container">
        <div class="register-form">
            <span class="back-btn" onclick="showHome()">← Back</span>
            <h2>Log In</h2>

            <form action="login.php" method="POST">
                <input type="text" name="student_id" placeholder="ID Number" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" class="btn-primary">Log In</button>
            </form>
        </div>
    </div>
</section>

<script src="script.js"></script>

<?php
if (isset($_GET['show']) && $_GET['show'] === 'register') {
    echo "<script>showRegister();</script>";
}
if (isset($_GET['show']) && $_GET['show'] === 'login') {
    echo "<script>showLogin();</script>";
}
?>
</body>
</html>