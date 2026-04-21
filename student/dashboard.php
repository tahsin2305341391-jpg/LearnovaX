<?php
// ============================================
// LearnovaX - Student Dashboard
// student/dashboard.php
// ============================================
require_once '../config/db.php';
require_once '../config/auth.php';
requireRole('student');

$userId = getUserId();
$flash  = getFlash();

// ── Fetch enrolled courses ────────────────────
$stmt = $conn->prepare("
    SELECT c.*, u.name AS instructor_name, cat.name AS category_name, e.enrolled_at
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    JOIN users u ON c.instructor_id = u.id
    LEFT JOIN categories cat ON c.category_id = cat.id
    WHERE e.student_id = ?
    ORDER BY e.enrolled_at DESC
");
$stmt->execute([$userId]);
$enrolledCourses = $stmt->fetchAll();

$totalEnrolled = count($enrolledCourses);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard – LearnovaX</title>
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
            <a href="dashboard.php"  class="nav-item active">📊 Dashboard</a>
            <a href="browse.php"     class="nav-item">🔍 Browse Courses</a>
            <a href="my_courses.php" class="nav-item">📚 My Courses</a>
            <a href="cart.php"       class="nav-item">🛒 Cart</a>
            <a href="orders.php"     class="nav-item">📋 Order History</a>
            <a href="profile.php"    class="nav-item">👤 Profile</a>
        </nav>
        <div class="sidebar-footer">
            <a href="../auth/logout.php" class="btn-logout">🚪 Logout</a>
        </div>
    </aside>

    <!-- Main -->
    <main class="main-content">
        <div class="topbar">
            <h1 class="page-title">My Dashboard</h1>
            <div class="user-info">👤 Welcome, <strong><?php echo getUserName(); ?></strong></div>
        </div>

        <!-- Flash -->
        <?php if ($flash): ?>
            <div class="alert alert-<?php echo $flash['type']; ?>">
                <?php echo $flash['message']; ?>
            </div>
        <?php endif; ?>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card purple">
                <div class="stat-icon">📚</div>
                <div class="stat-info">
                    <span class="stat-number"><?php echo $totalEnrolled; ?></span>
                    <span class="stat-label">Enrolled Courses</span>
                </div>
            </div>
        </div>

        <!-- Enrolled Courses -->
        <div class="section-card">
            <div class="section-header">
                <h2 class="section-title">📚 My Courses</h2>
                <a href="browse.php" class="btn-primary">Browse More</a>
            </div>

            <?php if (empty($enrolledCourses)): ?>
                <div class="empty-state">
                    <p>You haven't enrolled in any courses yet.</p>
                    <a href="browse.php" class="btn-primary">Explore Courses</a>
                </div>
            <?php else: ?>
                <div class="course-grid">
                    <?php foreach ($enrolledCourses as $c): ?>
                    <div class="course-card">
                        <div class="course-thumb">
                            <img src="../assets/uploads/<?php echo htmlspecialchars($c['thumbnail']); ?>"
                                 alt="<?php echo htmlspecialchars($c['title']); ?>"
                                 onerror="this.src='../assets/uploads/default_course.png'">
                        </div>
                        <div class="course-info">
                            <span class="course-category"><?php echo htmlspecialchars($c['category_name'] ?? 'General'); ?></span>
                            <h3 class="course-title"><?php echo htmlspecialchars($c['title']); ?></h3>
                            <p class="course-instructor">by <?php echo htmlspecialchars($c['instructor_name']); ?></p>
                            <p class="course-enrolled-date">Enrolled: <?php echo date('d M Y', strtotime($c['enrolled_at'])); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </main>
</div>

</body>
</html>
