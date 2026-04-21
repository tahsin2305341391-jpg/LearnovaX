<?php
// ============================================
// LearnovaX - Auth Helper Functions
// config/auth.php
// ============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Check if user is logged in ──────────────
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// ── Require login, redirect if not ──────────
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: " . BASE_URL . "auth/login.php");
        exit();
    }
}

// ── Require specific role ────────────────────
function requireRole($role) {
    requireLogin();
    if ($_SESSION['role'] !== $role) {
        header("Location: " . BASE_URL . "auth/login.php?error=unauthorized");
        exit();
    }
}

// ── Get current user's role ──────────────────
function getUserRole() {
    return $_SESSION['role'] ?? null;
}

// ── Get current user's ID ────────────────────
function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

// ── Get current user's name ──────────────────
function getUserName() {
    return $_SESSION['name'] ?? 'User';
}

// ── Redirect based on role after login ───────
function redirectByRole($role) {
    switch ($role) {
        case 'admin':
            header("Location: " . BASE_URL . "admin/dashboard.php");
            break;
        case 'instructor':
            header("Location: " . BASE_URL . "instructor/dashboard.php");
            break;
        case 'student':
            header("Location: " . BASE_URL . "student/dashboard.php");
            break;
        default:
            header("Location: " . BASE_URL . "index.php");
    }
    exit();
}

// ── Sanitize user input ───────────────────────
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// ── Flash message system ──────────────────────
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Base URL (adjust if subfolder differs)
define('BASE_URL', '/LearnovaX/');
?>
