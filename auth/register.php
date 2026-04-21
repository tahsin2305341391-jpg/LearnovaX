<?php
// ============================================
// LearnovaX - Registration Page
// auth/register.php
// ============================================
require_once '../config/db.php';
require_once '../config/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirectByRole(getUserRole());
}

$errors = [];
$success = '';
$name = $email = $role = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Sanitize inputs
    $name     = sanitize($_POST['name'] ?? '');
    $email    = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $role     = sanitize($_POST['role'] ?? '');

    // ── Validation ────────────────────────────
    if (empty($name)) {
        $errors[] = "Full name is required.";
    } elseif (!preg_match("/^[a-zA-Z\s]+$/", $name)) {
        $errors[] = "Name must contain letters only.";
    }

    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }

    if ($password !== $confirm) {
        $errors[] = "Passwords do not match.";
    }

    if (!in_array($role, ['student', 'instructor'])) {
        $errors[] = "Please select a valid role.";
    }

    // ── Check if email already exists ─────────
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $errors[] = "This email is already registered.";
        }
    }

    // ── Insert into database ──────────────────
    if (empty($errors)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare(
            "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$name, $email, $hashed, $role]);

        setFlash('success', 'Registration successful! Please log in.');
        header("Location: login.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register – LearnovaX</title>
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

        <h2 class="auth-title">Create Account</h2>
        <p class="auth-subtitle">Join LearnovaX and start learning today</p>

        <!-- Error Messages -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $e): ?>
                        <li><?php echo $e; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Register Form -->
        <form action="register.php" method="POST" id="registerForm" novalidate>

            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name"
                       placeholder="Enter your full name"
                       value="<?php echo $name; ?>" required>
                <span class="field-error" id="nameErr"></span>
            </div>

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
                           placeholder="Min. 6 characters" required>
                    <span class="toggle-pw" onclick="togglePassword('password')">👁</span>
                </div>
                <span class="field-error" id="passwordErr"></span>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <div class="input-icon-wrap">
                    <input type="password" id="confirm_password" name="confirm_password"
                           placeholder="Re-enter your password" required>
                    <span class="toggle-pw" onclick="togglePassword('confirm_password')">👁</span>
                </div>
                <span class="field-error" id="confirmErr"></span>
            </div>

            <div class="form-group">
                <label for="role">Register As</label>
                <select id="role" name="role" required>
                    <option value="" disabled selected>Select your role</option>
                    <option value="student"    <?php echo $role === 'student'     ? 'selected' : ''; ?>>Student – I want to learn</option>
                    <option value="instructor" <?php echo $role === 'instructor'  ? 'selected' : ''; ?>>Instructor – I want to teach</option>
                </select>
                <span class="field-error" id="roleErr"></span>
            </div>

            <button type="submit" class="btn-auth">Create Account</button>

        </form>

        <p class="auth-switch">Already have an account? <a href="login.php">Log In</a></p>

    </div>
</div>

<script src="../assets/js/auth.js"></script>
</body>
</html>
