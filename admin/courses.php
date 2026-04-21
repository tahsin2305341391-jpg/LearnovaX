<?php
// ============================================
// LearnovaX - Admin Manage Courses
// admin/courses.php
// ============================================
require_once '../config/db.php';
require_once '../config/auth.php';
requireRole('admin');

// ── Handle Approve / Reject / Delete ─────────
if (isset($_GET['approve'])) {
    $id = (int)$_GET['approve'];
    $conn->prepare("UPDATE courses SET status='approved' WHERE id=?")->execute([$id]);
    setFlash('success', 'Course approved successfully.');
    header("Location: courses.php"); exit();
}

if (isset($_GET['reject'])) {
    $id = (int)$_GET['reject'];
    $conn->prepare("UPDATE courses SET status='rejected' WHERE id=?")->execute([$id]);
    setFlash('success', 'Course rejected.');
    header("Location: courses.php"); exit();
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    // Get thumbnail to delete file
    $s = $conn->prepare("SELECT thumbnail FROM courses WHERE id=?");
    $s->execute([$id]);
    $c = $s->fetch();
    if ($c && $c['thumbnail'] !== 'default_course.png') {
        $path = '../assets/uploads/' . $c['thumbnail'];
        if (file_exists($path)) unlink($path);
    }
    $conn->prepare("DELETE FROM courses WHERE id=?")->execute([$id]);
    setFlash('success', 'Course deleted.');
    header("Location: courses.php"); exit();
}

$flash = getFlash();

// ── Filter by status ──────────────────────────
$filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$where  = $filter !== 'all' ? "WHERE c.status = '$filter'" : "";

$courses = $conn->query("
    SELECT c.*, u.name AS instructor_name, cat.name AS category_name,
    (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) AS enrolled_count
    FROM courses c
    JOIN users u ON c.instructor_id = u.id
    LEFT JOIN categories cat ON c.category_id = cat.id
    $where
    ORDER BY c.created_at DESC
")->fetchAll();

// Counts
$countAll      = $conn->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$countPending  = $conn->query("SELECT COUNT(*) FROM courses WHERE status='pending'")->fetchColumn();
$countApproved = $conn->query("SELECT COUNT(*) FROM courses WHERE status='approved'")->fetchColumn();
$countRejected = $conn->query("SELECT COUNT(*) FROM courses WHERE status='rejected'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses – LearnovaX</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="sidebar-logo">
            <span class="logo-learn">Learnova</span><span class="logo-x">X</span>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php"    class="nav-item">📊 Dashboard</a>
            <a href="users.php"        class="nav-item">👥 Manage Users</a>
            <a href="courses.php"      class="nav-item active">📚 Manage Courses</a>
            <a href="enrollments.php"  class="nav-item">📋 Enrollments</a>
        </nav>
        <div class="sidebar-footer">
            <a href="../auth/logout.php" class="btn-logout">🚪 Logout</a>
        </div>
    </aside>

    <main class="main-content">
        <div class="topbar">
            <h1 class="page-title">📚 Manage Courses</h1>
            <div class="user-info">👤 <strong><?php echo getUserName(); ?></strong></div>
        </div>

        <?php if ($flash): ?>
            <div class="alert alert-<?php echo $flash['type']; ?>"><?php echo $flash['message']; ?></div>
        <?php endif; ?>

        <!-- Filter Tabs -->
        <div class="filter-tabs">
            <a href="courses.php?status=all"      class="tab <?php echo $filter === 'all'      ? 'active' : ''; ?>">All (<?php echo $countAll; ?>)</a>
            <a href="courses.php?status=pending"  class="tab <?php echo $filter === 'pending'  ? 'active' : ''; ?>">⏳ Pending (<?php echo $countPending; ?>)</a>
            <a href="courses.php?status=approved" class="tab <?php echo $filter === 'approved' ? 'active' : ''; ?>">✅ Approved (<?php echo $countApproved; ?>)</a>
            <a href="courses.php?status=rejected" class="tab <?php echo $filter === 'rejected' ? 'active' : ''; ?>">❌ Rejected (<?php echo $countRejected; ?>)</a>
        </div>

        <div class="section-card">
            <?php if (empty($courses)): ?>
                <div class="empty-state"><p>No courses found.</p></div>
            <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Thumbnail</th>
                        <th>Title</th>
                        <th>Instructor</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Students</th>
                        <th>Status</th>
                        <th>Date</th>
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
                        <td><?php echo htmlspecialchars($c['instructor_name']); ?></td>
                        <td><?php echo htmlspecialchars($c['category_name'] ?? '—'); ?></td>
                        <td><?php echo $c['price'] > 0 ? '$' . number_format($c['price'], 2) : 'Free'; ?></td>
                        <td><?php echo $c['enrolled_count']; ?></td>
                        <td>
                            <span class="badge badge-<?php echo $c['status']; ?>">
                                <?php echo ucfirst($c['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('d M Y', strtotime($c['created_at'])); ?></td>
                        <td>
                            <?php if ($c['status'] === 'pending'): ?>
                                <a href="courses.php?approve=<?php echo $c['id']; ?>" class="btn-approve">Approve</a>
                                <a href="courses.php?reject=<?php echo $c['id']; ?>"  class="btn-reject">Reject</a>
                            <?php elseif ($c['status'] === 'rejected'): ?>
                                <a href="courses.php?approve=<?php echo $c['id']; ?>" class="btn-approve">Approve</a>
                            <?php elseif ($c['status'] === 'approved'): ?>
                                <a href="courses.php?reject=<?php echo $c['id']; ?>" class="btn-reject">Reject</a>
                            <?php endif; ?>
                            <a href="courses.php?delete=<?php echo $c['id']; ?>"
                               class="btn-sm btn-delete"
                               onclick="return confirm('Permanently delete this course?')">Delete</a>
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
