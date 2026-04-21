<?php
// ============================================
// LearnovaX - Delete Course
// instructor/delete_course.php
// ============================================
require_once '../config/db.php';
require_once '../config/auth.php';
requireRole('instructor');

$courseId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Verify course belongs to this instructor
$stmt = $conn->prepare("SELECT * FROM courses WHERE id = ? AND instructor_id = ?");
$stmt->execute([$courseId, getUserId()]);
$course = $stmt->fetch();

if (!$course) {
    setFlash('error', 'Course not found or access denied.');
    header("Location: dashboard.php");
    exit();
}

// Delete thumbnail file if not default
if ($course['thumbnail'] !== 'default_course.png') {
    $path = '../assets/uploads/' . $course['thumbnail'];
    if (file_exists($path)) unlink($path);
}

// Delete course (enrollments/reviews deleted via CASCADE)
$stmt = $conn->prepare("DELETE FROM courses WHERE id = ? AND instructor_id = ?");
$stmt->execute([$courseId, getUserId()]);

setFlash('success', 'Course deleted successfully.');
header("Location: dashboard.php");
exit();
?>
