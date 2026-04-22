<?php
// ============================================
// LearnovaX - Order History
// student/orders.php
// ============================================
require_once '../config/db.php';
require_once '../config/auth.php';
requireRole('student');

$studentId = getUserId();

$stmt = $conn->prepare("
    SELECT e.*, c.title AS course_title, c.price, c.thumbnail,
           c.level, u.name AS instructor_name,
           cat.name AS category_name
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    JOIN users u ON c.instructor_id = u.id
    LEFT JOIN categories cat ON c.category_id = cat.id
    WHERE e.student_id = ?
    ORDER BY e.enrolled_at DESC
");
$stmt->execute([$studentId]);
$orders = $stmt->fetchAll();

$totalSpent = array_sum(array_column($orders, 'price'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History – LearnovaX</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
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
            <a href="my_courses.php" class="nav-item">📚 My Courses</a>
            <a href="cart.php"       class="nav-item">🛒 Cart</a>
            <a href="orders.php"     class="nav-item active">📋 Order History</a>
            <a href="profile.php"    class="nav-item">👤 Profile</a>
        </nav>
        <div class="sidebar-footer">
            <a href="../auth/logout.php" class="btn-logout">🚪 Logout</a>
        </div>
    </aside>

    <main class="main-content">
        <div class="topbar">
            <h1 class="page-title">📋 Order History</h1>
            <div class="user-info">👤 <strong><?php echo getUserName(); ?></strong></div>
        </div>

        <!-- Summary -->
        <div class="stats-grid" style="margin-bottom:24px;">
            <div class="stat-card blue">
                <div class="stat-icon">📋</div>
                <div class="stat-info">
                    <span class="stat-number"><?php echo count($orders); ?></span>
                    <span class="stat-label">Total Orders</span>
                </div>
            </div>
            <div class="stat-card green">
                <div class="stat-icon">💰</div>
                <div class="stat-info">
                    <span class="stat-number">$<?php echo number_format($totalSpent, 2); ?></span>
                    <span class="stat-label">Total Spent</span>
                </div>
            </div>
        </div>

        <div class="section-card">
            <?php if (empty($orders)): ?>
                <div class="empty-state">
                    <p>You haven't made any purchases yet.</p>
                    <a href="browse.php" class="btn-primary">Browse Courses</a>
                </div>
            <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Course</th>
                        <th>Instructor</th>
                        <th>Category</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $i => $o): ?>
                    <tr>
                        <td><?php echo $i + 1; ?></td>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <img src="../assets/uploads/<?php echo htmlspecialchars($o['thumbnail']); ?>"
                                     onerror="this.src='../assets/uploads/default_course.png'"
                                     style="width:48px;height:32px;object-fit:cover;border-radius:4px;">
                                <span><?php echo htmlspecialchars($o['course_title']); ?></span>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($o['instructor_name']); ?></td>
                        <td><?php echo htmlspecialchars($o['category_name'] ?? '—'); ?></td>
                        <td><?php echo $o['price'] > 0 ? '$' . number_format($o['price'], 2) : 'Free'; ?></td>
                        <td>
                            <span class="badge <?php echo $o['payment_status'] === 'paid' ? 'badge-approved' : 'badge-pending'; ?>">
                                <?php echo ucfirst($o['payment_status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('d M Y, h:i A', strtotime($o['enrolled_at'])); ?></td>
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
