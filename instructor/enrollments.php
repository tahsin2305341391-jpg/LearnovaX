<?php
// ============================================
// LearnovaX - Instructor Enrollments
// instructor/enrollments.php
// ============================================
require_once '../config/db.php';
require_once '../config/auth.php';
requireRole('instructor');

$stmt = $conn->prepare("
    SELECT e.*, u.name AS student_name, u.email AS student_email,
           c.title AS course_title, c.price
    FROM enrollments e
    JOIN users u ON e.student_id = u.id
    JOIN courses c ON e.course_id = c.id
    WHERE c.instructor_id = ?
    ORDER BY e.enrolled_at DESC
");
$stmt->execute([getUserId()]);
$enrollments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollments – LearnovaX</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="sidebar-logo">
            <span class="logo-learn">Learnova</span><span class="logo-x">X</span>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php"     class="nav-item">📊 Dashboard</a>
            <a href="create_course.php" class="nav-item">➕ Create Course</a>
            <a href="my_courses.php"    class="nav-item">📚 My Courses</a>
            <a href="enrollments.php"   class="nav-item active">👥 Enrollments</a>
            <a href="profile.php"       class="nav-item">👤 Profile</a>
        </nav>
        <div class="sidebar-footer">
            <a href="../auth/logout.php" class="btn-logout">🚪 Logout</a>
        </div>
    </aside>

    <main class="main-content">
        <div class="topbar">
            <h1 class="page-title">👥 My Students</h1>
            <div class="user-info">👤 <strong><?php echo getUserName(); ?></strong></div>
        </div>

        <div class="section-card">
            <h2 class="section-title" style="margin-bottom:20px;">
                All Enrollments (<?php echo count($enrollments); ?>)
            </h2>

            <?php if (empty($enrollments)): ?>
                <div class="empty-state"><p>No students enrolled in your courses yet.</p></div>
            <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student</th>
                        <th>Email</th>
                        <th>Course</th>
                        <th>Amount</th>
                        <th>Payment</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($enrollments as $i => $e): ?>
                    <tr>
                        <td><?php echo $i + 1; ?></td>
                        <td><?php echo htmlspecialchars($e['student_name']); ?></td>
                        <td><?php echo htmlspecialchars($e['student_email']); ?></td>
                        <td><?php echo htmlspecialchars($e['course_title']); ?></td>
                        <td><?php echo $e['price'] > 0 ? '$' . number_format($e['price'], 2) : 'Free'; ?></td>
                        <td>
                            <span class="badge <?php echo $e['payment_status'] === 'paid' ? 'badge-approved' : 'badge-pending'; ?>">
                                <?php echo ucfirst($e['payment_status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('d M Y', strtotime($e['enrolled_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </main>
</div>
</body>
</html>
