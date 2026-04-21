<?php
// ============================================
// LearnovaX - Logout
// auth/logout.php
// ============================================
require_once '../config/auth.php';

// Destroy session
$_SESSION = [];
session_destroy();

// Redirect to login
header("Location: ../auth/login.php");
exit();
?>
