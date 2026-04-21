<?php
// ============================================
// LearnovaX - Instructor My Courses
// instructor/my_courses.php
// ============================================
require_once '../config/db.php';
require_once '../config/auth.php';
requireRole('instructor');

$flash = getFlash();

$stmt = $conn->prepare("
    SELECT c.*, cat.name AS category_name,
    (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) AS enrolled_count,
    (SELECT COALESCE(AVG(rating),0) FROM reviews WHERE course_id = c.id) AS avg_rating
    FROM courses c
    LEFT JOIN categories cat ON c.category_id = cat.id
    WHERE c.instructor_id = ?
    ORDER BY c.created_at DESC
");
$stmt->execute([getUserId()]);
$courses = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses – LearnovaX</title>
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
            <a href="my_courses.php"    class="nav-item active">📚 My Courses</a>
            <a href="enrollments.php"   class="nav-item">👥 Enrollments</a>
            <a href="profile.php"       class="nav-item">👤 Profile</a>
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

        <div class="section-card">
            <div class="section-header">
                <h2 class="section-title">All Courses (<?php echo count($courses); ?>)</h2>
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
                        <th>Thumbnail</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Level</th>
                        <th>Price</th>
                        <th>Students</th>
                        <th>Rating</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($courses as $i => $c): ?>
                    <tr>
                        <td><?php echo $i + 1; ?></td>
                        <td>
                            <img src="../assets/uploads/<?php echo htmlspecialchars($c['thumbnail']); ?>"
                                 onerror="this.src='../assets/uploads/default_course.png'"
                                 style="width:60px;height:40px;object-fit:cover;border-radius:6px;">
                        </td>
                        <td><?php echo htmlspecialchars($c['title']); ?></td>
                        <td><?php echo htmlspecialchars($c['category_name'] ?? '—'); ?></td>
                        <td><?php echo ucfirst($c['level']); ?></td>
                        <td><?php echo $c['price'] > 0 ? '$' . number_format($c['price'], 2) : 'Free'; ?></td>
                        <td><?php echo $c['enrolled_count']; ?></td>
                        <td>
                            <?php
                            $r = round($c['avg_rating'], 1);
                            echo $r > 0 ? "⭐ $r" : "—";
                            ?>
                        </td>
                        <td>
                            <span class="badge badge-<?php echo $c['status']; ?>">
                                <?php echo ucfirst($c['status']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="edit_course.php?id=<?php echo $c['id']; ?>" class="btn-sm btn-edit">Edit</a>
                            <a href="delete_course.php?id=<?php echo $c['id']; ?>"
                               class="btn-sm btn-delete"
                               onclick="return confirm('Are you sure you want to delete this course? This cannot be undone.')">
                               Delete
                            </a>
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
