<?php
// ============================================
// LearnovaX - Instructor Dashboard
// instructor/dashboard.php
// ============================================
require_once '../config/db.php';
require_once '../config/auth.php';
requireRole('instructor');

$userId = getUserId();

// ── Fetch stats ───────────────────────────────
$myCourses     = $conn->prepare("SELECT COUNT(*) FROM courses WHERE instructor_id = ?");
$myCourses->execute([$userId]);
$totalCourses  = $myCourses->fetchColumn();

$myEnrollments = $conn->prepare("
    SELECT COUNT(*) FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    WHERE c.instructor_id = ?
");
$myEnrollments->execute([$userId]);
$totalEnrollments = $myEnrollments->fetchColumn();

$myRevenue = $conn->prepare("
    SELECT COALESCE(SUM(c.price), 0) FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    WHERE c.instructor_id = ? AND e.payment_status = 'paid'
");
$myRevenue->execute([$userId]);
$totalRevenue = $myRevenue->fetchColumn();

// ── My courses list ───────────────────────────
$stmt = $conn->prepare("
    SELECT c.*, cat.name AS category_name,
    (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) AS enrolled_count
    FROM courses c
    LEFT JOIN categories cat ON c.category_id = cat.id
    WHERE c.instructor_id = ?
    ORDER BY c.created_at DESC
");
$stmt->execute([$userId]);
$courses = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Dashboard – LearnovaX</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>

<div class="layout">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-logo">
            <span class="logo-learn">Learnova</span><span class="logo-x">X</span>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php"     class="nav-item active">📊 Dashboard</a>
            <a href="create_course.php" class="nav-item">➕ Create Course</a>
            <a href="my_courses.php"    class="nav-item">📚 My Courses</a>
            <a href="enrollments.php"   class="nav-item">👥 Enrollments</a>
            <a href="profile.php"       class="nav-item">👤 Profile</a>
        </nav>
        <div class="sidebar-footer">
            <a href="../auth/logout.php" class="btn-logout">🚪 Logout</a>
        </div>
    </aside>

    <!-- Main -->
    <main class="main-content">
        <div class="topbar">
            <h1 class="page-title">Instructor Dashboard</h1>
            <div class="user-info">👤 Welcome, <strong><?php echo getUserName(); ?></strong></div>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card purple">
                <div class="stat-icon">📚</div>
                <div class="stat-info">
                    <span class="stat-number"><?php echo $totalCourses; ?></span>
                    <span class="stat-label">My Courses</span>
                </div>
            </div>
            <div class="stat-card blue">
                <div class="stat-icon">🎓</div>
                <div class="stat-info">
                    <span class="stat-number"><?php echo $totalEnrollments; ?></span>
                    <span class="stat-label">Total Students</span>
                </div>
            </div>
            <div class="stat-card green">
                <div class="stat-icon">💰</div>
                <div class="stat-info">
                    <span class="stat-number">$<?php echo number_format($totalRevenue, 2); ?></span>
                    <span class="stat-label">Total Revenue</span>
                </div>
            </div>
        </div>

        <!-- My Courses Table -->
        <div class="section-card">
            <div class="section-header">
                <h2 class="section-title">📚 My Courses</h2>
                <a href="create_course.php" class="btn-primary">+ New Course</a>
            </div>

            <?php if (empty($courses)): ?>
                <div class="empty-state">
                    <p>You haven't created any courses yet.</p>
                    <a href="create_course.php" class="btn-primary">Create Your First Course</a>
                </div>
            <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Students</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($courses as $i => $c): ?>
                    <tr>
                        <td><?php echo $i + 1; ?></td>
                        <td><?php echo htmlspecialchars($c['title']); ?></td>
                        <td><?php echo htmlspecialchars($c['category_name'] ?? 'Uncategorized'); ?></td>
                        <td>$<?php echo number_format($c['price'], 2); ?></td>
                        <td><?php echo $c['enrolled_count']; ?></td>
                        <td>
                            <span class="badge badge-<?php echo $c['status']; ?>">
                                <?php echo ucfirst($c['status']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="edit_course.php?id=<?php echo $c['id']; ?>" class="btn-sm btn-edit">Edit</a>
                            <a href="delete_course.php?id=<?php echo $c['id']; ?>"
                               class="btn-sm btn-delete"
                               onclick="return confirm('Delete this course?')">Delete</a>
                        </td>
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
