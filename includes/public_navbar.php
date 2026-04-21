<?php
// ============================================
// LearnovaX - Shared Public Navbar
// includes/public_navbar.php
// ============================================
// Usage: include this at the top of any public page
// Set $activePage before including:
//   $activePage = 'home'        → index.php
//   $activePage = 'developers'  → developers.php
// ============================================
if (!isset($activePage)) $activePage = '';
?>
<nav class="navbar">
    <div class="nav-container">
        <a href="index.php" class="nav-logo">
            <span class="logo-learn">Learnova</span><span class="logo-x">X</span>
        </a>

        <ul class="nav-links">
            <li>
                <a href="index.php#courses"
                   class="<?php echo $activePage === 'home' ? 'nav-active' : ''; ?>">
                   Courses
                </a>
            </li>
            <li>
                <a href="index.php#categories">Categories</a>
            </li>
            <li>
                <a href="index.php#about">About</a>
            </li>
            <li>
                <a href="index.php#testimonials">Reviews</a>
            </li>
            <li>
                <a href="developers.php"
                   class="<?php echo $activePage === 'developers' ? 'nav-active' : ''; ?>">
                   Our Team
                </a>
            </li>
        </ul>

        <div class="nav-actions">
            <a href="auth/login.php"    class="btn-nav-outline">Log In</a>
            <a href="auth/register.php" class="btn-nav-solid">Get Started</a>
        </div>

        <button class="hamburger" id="hamburger" aria-label="Menu">
            <span></span><span></span><span></span>
        </button>
    </div>

    <!-- Mobile Menu -->
    <div class="mobile-menu" id="mobileMenu">
        <a href="index.php">🏠 Home</a>
        <a href="index.php#courses">📚 Courses</a>
        <a href="index.php#categories">🗂️ Categories</a>
        <a href="index.php#about">💡 About</a>
        <a href="index.php#testimonials">💬 Reviews</a>
        <a href="developers.php" class="<?php echo $activePage === 'developers' ? 'mobile-nav-active' : ''; ?>">
            👨‍💻 Our Team
        </a>
        <div class="mobile-divider"></div>
        <a href="auth/login.php">Log In</a>
        <a href="auth/register.php" class="mobile-cta">Get Started Free</a>
    </div>
</nav>
