<?php
// ============================================
// LearnovaX - Admin Dashboard
// admin/dashboard.php
// ============================================
require_once '../config/db.php';
require_once '../config/auth.php';
requireRole('admin');

// ── Fetch summary stats ───────────────────────
$totalUsers       = $conn->query("SELECT COUNT(*) FROM users WHERE role != 'admin'")->fetchColumn();
$totalStudents    = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$totalInstructors = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'instructor'")->fetchColumn();
$totalCourses     = $conn->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$pendingCourses   = $conn->query("SELECT COUNT(*) FROM courses WHERE status = 'pending'")->fetchColumn();
$totalEnrollments = $conn->query("SELECT COUNT(*) FROM enrollments")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard – LearnovaX</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>

<!-- Sidebar -->
<div class="layout">
    <aside class="sidebar">
        <div class="sidebar-logo">
            <span class="logo-learn">Learnova</span><span class="logo-x">X</span>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-item active">📊 Dashboard</a>
            <a href="users.php"     class="nav-item">👥 Manage Users</a>
            <a href="courses.php"   class="nav-item">📚 Manage Courses</a>
            <a href="enrollments.php" class="nav-item">📋 Enrollments</a>
        </nav>
        <div class="sidebar-footer">
            <a href="../auth/logout.php" class="btn-logout">🚪 Logout</a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="topbar">
            <h1 class="page-title">Admin Dashboard</h1>
            <div class="user-info">
                👤 Welcome, <strong><?php echo getUserName(); ?></strong>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card purple">
                <div class="stat-icon">👥</div>
                <div class="stat-info">
                    <span class="stat-number"><?php echo $totalUsers; ?></span>
                    <span class="stat-label">Total Users</span>
                </div>
            </div>
            <div class="stat-card blue">
                <div class="stat-icon">🎓</div>
                <div class="stat-info">
                    <span class="stat-number"><?php echo $totalStudents; ?></span>
                    <span class="stat-label">Students</span>
                </div>
            </div>
            <div class="stat-card green">
                <div class="stat-icon">🧑‍🏫</div>
                <div class="stat-info">
                    <span class="stat-number"><?php echo $totalInstructors; ?></span>
                    <span class="stat-label">Instructors</span>
                </div>
            </div>
            <div class="stat-card orange">
                <div class="stat-icon">📚</div>
                <div class="stat-info">
                    <span class="stat-number"><?php echo $totalCourses; ?></span>
                    <span class="stat-label">Total Courses</span>
                </div>
            </div>
            <div class="stat-card red">
                <div class="stat-icon">⏳</div>
                <div class="stat-info">
                    <span class="stat-number"><?php echo $pendingCourses; ?></span>
                    <span class="stat-label">Pending Approval</span>
                </div>
            </div>
            <div class="stat-card teal">
                <div class="stat-icon">📋</div>
                <div class="stat-info">
                    <span class="stat-number"><?php echo $totalEnrollments; ?></span>
                    <span class="stat-label">Enrollments</span>
                </div>
            </div>
        </div>

        <!-- Pending Courses -->
        <?php if ($pendingCourses > 0): ?>
        <div class="section-card">
            <h2 class="section-title">⏳ Courses Awaiting Approval</h2>
            <?php
            $stmt = $conn->query("
                SELECT c.*, u.name AS instructor_name
                FROM courses c
                JOIN users u ON c.instructor_id = u.id
                WHERE c.status = 'pending'
                ORDER BY c.created_at DESC
                LIMIT 5
            ");
            $pending = $stmt->fetchAll();
            ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Course Title</th>
                        <th>Instructor</th>
                        <th>Price</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending as $i => $course): ?>
                    <tr>
                        <td><?php echo $i + 1; ?></td>
                        <td><?php echo htmlspecialchars($course['title']); ?></td>
                        <td><?php echo htmlspecialchars($course['instructor_name']); ?></td>
                        <td>$<?php echo number_format($course['price'], 2); ?></td>
                        <td><?php echo date('d M Y', strtotime($course['created_at'])); ?></td>
                        <td>
                            <a href="courses.php?approve=<?php echo $course['id']; ?>" class="btn-approve">Approve</a>
                            <a href="courses.php?reject=<?php echo $course['id']; ?>"  class="btn-reject">Reject</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <a href="courses.php" class="view-all">View all courses →</a>
        </div>
        <?php endif; ?>

    </main>
</div>

</body>
</html>
