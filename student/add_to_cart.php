<?php
// ============================================
// LearnovaX - Add to Cart
// student/add_to_cart.php
// ============================================
require_once '../config/db.php';
require_once '../config/auth.php';
requireRole('student');

$courseId  = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$studentId = getUserId();

// Validate course exists and is approved
$stmt = $conn->prepare("SELECT id FROM courses WHERE id = ? AND status = 'approved'");
$stmt->execute([$courseId]);
if (!$stmt->fetch()) {
    setFlash('error', 'Course not found.');
    header("Location: browse.php"); exit();
}

// Check already enrolled
$stmt = $conn->prepare("SELECT id FROM enrollments WHERE student_id=? AND course_id=?");
$stmt->execute([$studentId, $courseId]);
if ($stmt->rowCount() > 0) {
    setFlash('error', 'You are already enrolled in this course.');
    header("Location: browse.php"); exit();
}

// Check already in cart
$stmt = $conn->prepare("SELECT id FROM cart WHERE student_id=? AND course_id=?");
$stmt->execute([$studentId, $courseId]);
if ($stmt->rowCount() > 0) {
    setFlash('error', 'Course is already in your cart.');
    header("Location: cart.php"); exit();
}

// Add to cart
$stmt = $conn->prepare("INSERT INTO cart (student_id, course_id) VALUES (?,?)");
$stmt->execute([$studentId, $courseId]);

setFlash('success', 'Course added to cart!');
header("Location: cart.php");
exit();
?>
