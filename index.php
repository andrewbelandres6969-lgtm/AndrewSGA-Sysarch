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
        <a href="reports.php">Community</a>

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

<section id="homeSection" class="hero fade-section">
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

<section id="registerSection" class="hidden fade-section">
    <div class="register-container">
        <div class="register-image">
            <img src="registerGift.gif" alt="Register">
        </div>

        <div class="register-form">
            <span class="back-btn" onclick="showHome()">← Back</span>
            <h2>Sign Up</h2>

            <form action="register.php" method="POST">
                <input type="number" name="student_id" placeholder="ID Number" step="1" min="0" required>
                <input type="text" name="last_name" placeholder="Last Name" required>
                <input type="text" name="first_name" placeholder="First Name" required>
                <input type="text" name="middle_name" placeholder="Middle Name">
                <select name="course_level" required>
                    <option value="">Select Course Level</option>
                    <option value="1">1st Year</option>
                    <option value="2">2nd Year</option>
                    <option value="3">3rd Year</option>
                    <option value="4">4th Year</option>
                </select>
                <input type="password" name="password" placeholder="Password" required>
                <input type="password" name="repeat_password" placeholder="Repeat Password" required>
                <input type="email" name="email" placeholder="Email">

                <select name="course" required>
                    <option value="">Select Course</option>

                    <optgroup label="IT / Computer Courses">
                        <option value="BS CS">BS CS (BACHELOR OF SCIENCE IN COMPUTER SCIENCE)</option>
                        <option value="BS IT">BS IT (BACHELOR OF SCIENCE IN INFORMATION TECHNOLOGY)</option>
                        <option value="BS CIS">BS CIS (BACHELOR OF SCIENCE IN COMPUTER INFORMATION SYSTEMS)</option>
                        <option value="BSED InfoTech">BSED InfoTech</option>
                    </optgroup>

                    <optgroup label="Engineering">
                        <option value="BSCE">BSCE</option>
                        <option value="BSEE">BSEE</option>
                        <option value="BSME">BSME</option>
                        <option value="BSECE">BSECE</option>
                    </optgroup>

                    <optgroup label="Business">
                        <option value="BSBA">BSBA</option>
                        <option value="BSA">BSA</option>
                        <option value="BSMA">BSMA</option>
                        <option value="BSHM">BSHM</option>
                        <option value="BSTM">BSTM</option>
                    </optgroup>

                    <optgroup label="Education">
                        <option value="BEED">BEED</option>
                        <option value="BSED English">BSED English</option>
                        <option value="BSED Math">BSED Math</option>
                    </optgroup>

                    <optgroup label="Health / Science">
                        <option value="BSN">BSN</option>
                        <option value="BSMLS">BSMLS</option>
                        <option value="BSPH">BSPH</option>
                        <option value="BSBio">BSBio</option>
                    </optgroup>

                    <optgroup label="Arts & Social Sciences">
                        <option value="BA Comm">BA Comm</option>
                        <option value="BA Psych">BA Psych</option>
                        <option value="BS Psych">BS Psych</option>
                        <option value="BA PolSci">BA PolSci</option>
                    </optgroup>

                    <optgroup label="Criminology">
                        <option value="BS Crim">BS Crim</option>
                    </optgroup>
                </select>

                <input type="text" name="address" placeholder="Address">
                <button type="submit" class="btn-primary">Register</button>
            </form>
        </div>
    </div>
</section>

<section id="loginSection" class="hidden fade-section">
    <div class="login-wrapper">
        <div class="login-card">

            <span class="back-btn" onclick="showHome()">← Back</span>

            <div class="login-content">

                <!-- LEFT: FORM -->
                <div class="login-text-side">
                    <h2>Welcome Back</h2>
                    <p>Log in to continue to the CCS Sit-In Monitoring System</p>

                    <form action="login.php" method="POST">
                        <input type="text" name="student_id" placeholder="ID Number" inputmode="numeric" required>
                        <input type="password" name="password" placeholder="Password" required>

                        <button type="submit" class="btn-primary login-submit">
                            Log In
                        </button>
                        <p class="forgot-password"><a href="forgot_password.php" onclick="pageTransition('forgot_password.php'); return false;">Forgot password?</a></p>
                    </form>
                </div>

                <!-- RIGHT: GIF -->
                <div class="login-gif-side">
                    <img src="Luffy1.gif" alt="Login GIF">
                </div>

            </div>
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