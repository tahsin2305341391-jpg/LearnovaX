<?php
// ============================================
// LearnovaX - Shared Public Footer
// includes/public_footer.php
// ============================================
// Fetch live stats for footer if $conn available
$fCourses     = isset($conn) ? $conn->query("SELECT COUNT(*) FROM courses WHERE status='approved'")->fetchColumn() : 0;
$fStudents    = isset($conn) ? $conn->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn()    : 0;
$fInstructors = isset($conn) ? $conn->query("SELECT COUNT(*) FROM users WHERE role='instructor'")->fetchColumn(): 0;
?>
<footer class="footer">
    <div class="footer-container">

        <!-- Brand -->
        <div class="footer-brand">
            <div class="footer-logo">
                <span class="logo-learn">Learnova</span><span class="logo-x">X</span>
            </div>
            <p class="footer-tagline">Learn Smart, Grow Fast.</p>
            <p class="footer-desc">
                A modern educational marketplace connecting learners
                and instructors with structured, secure, and scalable
                learning tools.
            </p>
            <div class="footer-stats">
                <div class="footer-stat">
                    <span class="footer-stat-num"><?php echo $fCourses; ?>+</span>
                    <span class="footer-stat-lbl">Courses</span>
                </div>
                <div class="footer-stat">
                    <span class="footer-stat-num"><?php echo $fStudents; ?>+</span>
                    <span class="footer-stat-lbl">Students</span>
                </div>
                <div class="footer-stat">
                    <span class="footer-stat-num"><?php echo $fInstructors; ?>+</span>
                    <span class="footer-stat-lbl">Instructors</span>
                </div>
            </div>
        </div>

        <!-- Links -->
        <div class="footer-links">
            <div class="footer-col">
                <h4>Platform</h4>
                <a href="index.php">Home</a>
                <a href="index.php#courses">Browse Courses</a>
                <a href="index.php#categories">Categories</a>
                <a href="index.php#about">About Us</a>
                <a href="index.php#how">How It Works</a>
            </div>
            <div class="footer-col">
                <h4>Account</h4>
                <a href="auth/login.php">Log In</a>
                <a href="auth/register.php">Register</a>
                <a href="auth/register.php">Become Instructor</a>
            </div>
            <div class="footer-col">
                <h4>Team</h4>
                <a href="developers.php">Meet the Team</a>
                <a href="developers.php#member-1">Alex Johnson</a>
                <a href="developers.php#member-2">Sarah Williams</a>
                <a href="developers.php#member-3">Michael Chen</a>
                <a href="developers.php#member-4">Emily Rodriguez</a>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <p>
            © <?php echo date('Y'); ?> LearnovaX &nbsp;·&nbsp;
            Dept. of Software Engineering &nbsp;·&nbsp;
            Daffodil International University &nbsp;·&nbsp;
        </p>
        <div class="footer-bottom-links">
            <a href="index.php">Home</a>
            <a href="developers.php">Our Team</a>
            <a href="auth/login.php">Login</a>
        </div>
    </div>
</footer>
