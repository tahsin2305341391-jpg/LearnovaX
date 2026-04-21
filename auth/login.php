<?php
// ============================================
// LearnovaX - Login Page
// auth/login.php
// ============================================
require_once '../config/db.php';
require_once '../config/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirectByRole(getUserRole());
}

$error   = '';
$email   = '';

// Handle unauthorized redirect message
$urlError = '';
if (isset($_GET['error']) && $_GET['error'] === 'unauthorized') {
    $urlError = "You don't have permission to access that page.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email    = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // ── Basic validation ──────────────────────
    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // ── Fetch user from DB ─────────────────
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {

            // ── Check account status ───────────
            if ($user['status'] === 'inactive') {
                $error = "Your account has been deactivated. Please contact admin.";
            } else {
                // ── Set session variables ──────
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['name']     = $user['name'];
                $_SESSION['email']    = $user['email'];
                $_SESSION['role']     = $user['role'];
                $_SESSION['pic']      = $user['profile_pic'];

                // ── Redirect by role ───────────
                redirectByRole($user['role']);
            }

        } else {
            $error = "Invalid email or password.";
        }
    }
}

// Get flash message (e.g., after registration)
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – LearnovaX</title>
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body>

<div class="auth-wrapper">
    <div class="auth-card">

        <!-- Logo -->
        <div class="auth-logo">
            <a href="../index.php">
                <span class="logo-learn">Learnova</span><span class="logo-x">X</span>
            </a>
        </div>

        <h2 class="auth-title">Welcome Back</h2>
        <p class="auth-subtitle">Log in to continue your learning journey</p>

        <!-- Flash / Error Messages -->
        <?php if ($flash): ?>
            <div class="alert alert-<?php echo $flash['type']; ?>">
                <?php echo $flash['message']; ?>
            </div>
        <?php endif; ?>

        <?php if ($urlError): ?>
            <div class="alert alert-error"><?php echo $urlError; ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Login Form -->
        <form action="login.php" method="POST" id="loginForm" novalidate>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email"
                       placeholder="Enter your email"
                       value="<?php echo $email; ?>" required>
                <span class="field-error" id="emailErr"></span>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-icon-wrap">
                    <input type="password" id="password" name="password"
                           placeholder="Enter your password" required>
                    <span class="toggle-pw" onclick="togglePassword('password')">👁</span>
                </div>
                <span class="field-error" id="passwordErr"></span>
            </div>

            <button type="submit" class="btn-auth">Log In</button>

        </form>

        <p class="auth-switch">Don't have an account? <a href="register.php">Register</a></p>

    </div>
</div>

<script src="../assets/js/auth.js"></script>
</body>
</html>
