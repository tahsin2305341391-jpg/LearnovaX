<?php
// ============================================
// LearnovaX - Student My Courses
// student/my_courses.php
// ============================================
require_once '../config/db.php';
require_once '../config/auth.php';
requireRole('student');

$studentId = getUserId();
$flash     = getFlash();

$stmt = $conn->prepare("
    SELECT c.*, u.name AS instructor_name, cat.name AS category_name,
           e.enrolled_at, e.payment_status,
    (SELECT COALESCE(AVG(rating),0) FROM reviews WHERE course_id = c.id) AS avg_rating
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    JOIN users u ON c.instructor_id = u.id
    LEFT JOIN categories cat ON c.category_id = cat.id
    WHERE e.student_id = ?
    ORDER BY e.enrolled_at DESC
");
$stmt->execute([$studentId]);
$courses = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses – LearnovaX</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/browse.css">
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="sidebar-logo">
            <span class="logo-learn">Learnova</span><span class="logo-x">X</span>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php"  class="nav-item">📊 Dashboard</a>
            <a href="browse.php"     class="nav-item">🔍 Browse Courses</a>
            <a href="my_courses.php" class="nav-item active">📚 My Courses</a>
            <a href="cart.php"       class="nav-item">🛒 Cart</a>
            <a href="orders.php"     class="nav-item">📋 Order History</a>
            <a href="profile.php"    class="nav-item">👤 Profile</a>
        </nav>
        <div class="sidebar-footer">
            <a href="../auth/logout.php" class="btn-logout">🚪 Logout</a>
        </div>
    </aside>

    <main class="main-content">
        <div class="topbar">
            <h1 class="page-title">📚 My Courses</h1>
            <div class="user-info">👤 <strong><?php echo getUserName(); ?></strong></div>
        </div>

        <?php if ($flash): ?>
            <div class="alert alert-<?php echo $flash['type']; ?>"><?php echo $flash['message']; ?></div>
        <?php endif; ?>

        <?php if (empty($courses)): ?>
            <div class="empty-state">
                <div style="font-size:48px;margin-bottom:16px;">📚</div>
                <p>You haven't enrolled in any courses yet.</p>
                <a href="browse.php" class="btn-primary">Browse Courses</a>
            </div>
        <?php else: ?>
        <div class="browse-grid">
            <?php foreach ($courses as $c): ?>
            <div class="browse-card">
                <a href="course_detail.php?id=<?php echo $c['id']; ?>">
                    <img src="../assets/uploads/<?php echo htmlspecialchars($c['thumbnail']); ?>"
                         onerror="this.src='../assets/uploads/default_course.png'"
                         class="card-thumb" alt="">
                </a>
                <div class="card-body">
                    <div class="card-meta">
                        <span class="card-category"><?php echo htmlspecialchars($c['category_name'] ?? 'General'); ?></span>
                        <span class="card-level level-<?php echo $c['level']; ?>"><?php echo ucfirst($c['level']); ?></span>
                    </div>
                    <a href="course_detail.php?id=<?php echo $c['id']; ?>" class="card-title">
                        <?php echo htmlspecialchars($c['title']); ?>
                    </a>
                    <p class="card-instructor">by <?php echo htmlspecialchars($c['instructor_name']); ?></p>
                    <div class="card-stats">
                        <span>⭐ <?php echo $c['avg_rating'] > 0 ? number_format($c['avg_rating'],1) : 'No ratings'; ?></span>
                        <span>📅 <?php echo date('d M Y', strtotime($c['enrolled_at'])); ?></span>
                    </div>
                    <div class="card-footer">
                        <span class="badge badge-approved">✅ Enrolled</span>
                        <a href="course_detail.php?id=<?php echo $c['id']; ?>" class="btn-add-cart">View Course</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </main>
</div>
</body>
</html>
