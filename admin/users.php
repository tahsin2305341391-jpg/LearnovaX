<?php
// ============================================
// LearnovaX - Admin Manage Users
// admin/users.php
// ============================================
require_once '../config/db.php';
require_once '../config/auth.php';
requireRole('admin');

// ── Toggle user status ────────────────────────
if (isset($_GET['toggle'])) {
    $id   = (int)$_GET['toggle'];
    // Prevent admin from deactivating themselves
    if ($id !== getUserId()) {
        $s = $conn->prepare("SELECT status FROM users WHERE id=?");
        $s->execute([$id]);
        $u = $s->fetch();
        if ($u) {
            $newStatus = $u['status'] === 'active' ? 'inactive' : 'active';
            $conn->prepare("UPDATE users SET status=? WHERE id=?")->execute([$newStatus, $id]);
            setFlash('success', 'User status updated.');
        }
    } else {
        setFlash('error', 'You cannot deactivate your own account.');
    }
    header("Location: users.php"); exit();
}

// ── Delete user ───────────────────────────────
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id !== getUserId()) {
        $conn->prepare("DELETE FROM users WHERE id=?")->execute([$id]);
        setFlash('success', 'User deleted.');
    } else {
        setFlash('error', 'You cannot delete your own account.');
    }
    header("Location: users.php"); exit();
}

$flash = getFlash();

// ── Filter ────────────────────────────────────
$role   = isset($_GET['role'])   ? $_GET['role']   : 'all';
$where  = $role !== 'all' ? "WHERE role = '$role'" : "WHERE role != 'admin'";

$users = $conn->query("SELECT * FROM users $where ORDER BY created_at DESC")->fetchAll();

$countAll        = $conn->query("SELECT COUNT(*) FROM users WHERE role != 'admin'")->fetchColumn();
$countStudents   = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$countInstructors= $conn->query("SELECT COUNT(*) FROM users WHERE role = 'instructor'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users – LearnovaX</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="sidebar-logo">
            <span class="logo-learn">Learnova</span><span class="logo-x">X</span>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php"   class="nav-item">📊 Dashboard</a>
            <a href="users.php"       class="nav-item active">👥 Manage Users</a>
            <a href="courses.php"     class="nav-item">📚 Manage Courses</a>
            <a href="enrollments.php" class="nav-item">📋 Enrollments</a>
        </nav>
        <div class="sidebar-footer">
            <a href="../auth/logout.php" class="btn-logout">🚪 Logout</a>
        </div>
    </aside>

    <main class="main-content">
        <div class="topbar">
            <h1 class="page-title">👥 Manage Users</h1>
            <div class="user-info">👤 <strong><?php echo getUserName(); ?></strong></div>
        </div>

        <?php if ($flash): ?>
            <div class="alert alert-<?php echo $flash['type']; ?>"><?php echo $flash['message']; ?></div>
        <?php endif; ?>

        <!-- Filter Tabs -->
        <div class="filter-tabs">
            <a href="users.php?role=all"        class="tab <?php echo $role === 'all'        ? 'active' : ''; ?>">All (<?php echo $countAll; ?>)</a>
            <a href="users.php?role=student"    class="tab <?php echo $role === 'student'    ? 'active' : ''; ?>">🎓 Students (<?php echo $countStudents; ?>)</a>
            <a href="users.php?role=instructor" class="tab <?php echo $role === 'instructor' ? 'active' : ''; ?>">🧑‍🏫 Instructors (<?php echo $countInstructors; ?>)</a>
        </div>

        <div class="section-card">
            <?php if (empty($users)): ?>
                <div class="empty-state"><p>No users found.</p></div>
            <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $i => $u): ?>
                    <tr>
                        <td><?php echo $i + 1; ?></td>
                        <td><?php echo htmlspecialchars($u['name']); ?></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td>
                            <span class="badge <?php echo $u['role'] === 'instructor' ? 'badge-approved' : 'badge-pending'; ?>">
                                <?php echo ucfirst($u['role']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?php echo $u['status'] === 'active' ? 'badge-approved' : 'badge-rejected'; ?>">
                                <?php echo ucfirst($u['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('d M Y', strtotime($u['created_at'])); ?></td>
                        <td>
                            <a href="users.php?toggle=<?php echo $u['id']; ?>"
                               class="btn-sm <?php echo $u['status'] === 'active' ? 'btn-delete' : 'btn-approve'; ?>">
                               <?php echo $u['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>
                            </a>
                            <a href="users.php?delete=<?php echo $u['id']; ?>"
                               class="btn-sm btn-delete"
                               onclick="return confirm('Delete this user? All their data will be removed.')">
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
