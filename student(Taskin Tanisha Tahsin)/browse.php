<?php
// ============================================
// LearnovaX - Browse Courses
// student/browse.php
// ============================================
require_once '../config/db.php';
require_once '../config/auth.php';
requireRole('student');

$studentId = getUserId();
$flash     = getFlash();

// ── Search & Filter ───────────────────────────
$search   = sanitize($_GET['search']   ?? '');
$category = sanitize($_GET['category'] ?? '');
$level    = sanitize($_GET['level']    ?? '');
$sort     = sanitize($_GET['sort']     ?? 'newest');

// ── Build Query ───────────────────────────────
$where  = ["c.status = 'approved'"];
$params = [];

if (!empty($search)) {
    $where[]  = "(c.title LIKE ? OR c.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if (!empty($category)) {
    $where[]  = "c.category_id = ?";
    $params[] = $category;
}
if (!empty($level)) {
    $where[]  = "c.level = ?";
    $params[] = $level;
}

$orderBy = match($sort) {
    'price_low'  => "c.price ASC",
    'price_high' => "c.price DESC",
    'popular'    => "enrolled_count DESC",
    default      => "c.created_at DESC"
};

$whereStr = implode(' AND ', $where);

$stmt = $conn->prepare("
    SELECT c.*, u.name AS instructor_name, cat.name AS category_name,
    (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) AS enrolled_count,
    (SELECT COALESCE(AVG(rating),0) FROM reviews WHERE course_id = c.id) AS avg_rating,
    (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id AND student_id = ?) AS is_enrolled,
    (SELECT COUNT(*) FROM cart WHERE course_id = c.id AND student_id = ?) AS in_cart
    FROM courses c
    JOIN users u ON c.instructor_id = u.id
    LEFT JOIN categories cat ON c.category_id = cat.id
    WHERE $whereStr
    ORDER BY $orderBy
");

$stmt->execute(array_merge([$studentId, $studentId], $params));
$courses    = $stmt->fetchAll();
$categories = $conn->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Courses – LearnovaX</title>
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
            <a href="browse.php"     class="nav-item active">🔍 Browse Courses</a>
            <a href="my_courses.php" class="nav-item">📚 My Courses</a>
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
            <h1 class="page-title">🔍 Browse Courses</h1>
            <div class="user-info">👤 <strong><?php echo getUserName(); ?></strong></div>
        </div>

        <?php if ($flash): ?>
            <div class="alert alert-<?php echo $flash['type']; ?>"><?php echo $flash['message']; ?></div>
        <?php endif; ?>

        <!-- Search & Filter Bar -->
        <form method="GET" action="browse.php" class="filter-bar">
            <input type="text" name="search" placeholder="Search courses..."
                   value="<?php echo $search; ?>" class="search-input">

            <select name="category">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>"
                        <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="level">
                <option value="">All Levels</option>
                <option value="beginner"     <?php echo $level === 'beginner'     ? 'selected' : ''; ?>>Beginner</option>
                <option value="intermediate" <?php echo $level === 'intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                <option value="advanced"     <?php echo $level === 'advanced'     ? 'selected' : ''; ?>>Advanced</option>
            </select>

            <select name="sort">
                <option value="newest"     <?php echo $sort === 'newest'     ? 'selected' : ''; ?>>Newest</option>
                <option value="popular"    <?php echo $sort === 'popular'    ? 'selected' : ''; ?>>Most Popular</option>
                <option value="price_low"  <?php echo $sort === 'price_low'  ? 'selected' : ''; ?>>Price: Low to High</option>
                <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
            </select>

            <button type="submit" class="btn-primary">Filter</button>
            <?php if ($search || $category || $level || $sort !== 'newest'): ?>
                <a href="browse.php" class="btn-secondary">Clear</a>
            <?php endif; ?>
        </form>

        <!-- Results Count -->
        <p class="results-count">
            Showing <strong><?php echo count($courses); ?></strong> course<?php echo count($courses) !== 1 ? 's' : ''; ?>
            <?php if ($search): ?> for "<strong><?php echo htmlspecialchars($search); ?></strong>"<?php endif; ?>
        </p>

        <!-- Course Grid -->
        <?php if (empty($courses)): ?>
            <div class="empty-state">
                <p>😕 No courses found. Try different filters.</p>
                <a href="browse.php" class="btn-primary">View All Courses</a>
            </div>
        <?php else: ?>
        <div class="browse-grid">
            <?php foreach ($courses as $c): ?>
            <div class="browse-card">
                <a href="course_detail.php?id=<?php echo $c['id']; ?>" class="card-thumb-link">
                    <img src="../assets/uploads/<?php echo htmlspecialchars($c['thumbnail']); ?>"
                         onerror="this.src='../assets/uploads/default_course.png'"
                         alt="<?php echo htmlspecialchars($c['title']); ?>"
                         class="card-thumb">
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
                        <span>⭐ <?php echo $c['avg_rating'] > 0 ? number_format($c['avg_rating'], 1) : 'New'; ?></span>
                        <span>👥 <?php echo $c['enrolled_count']; ?> students</span>
                    </div>

                    <div class="card-footer">
                        <span class="card-price">
                            <?php echo $c['price'] > 0 ? '$' . number_format($c['price'], 2) : '<span class="free-badge">FREE</span>'; ?>
                        </span>

                        <?php if ($c['is_enrolled']): ?>
                            <span class="btn-enrolled">✅ Enrolled</span>
                        <?php elseif ($c['in_cart']): ?>
                            <a href="cart.php" class="btn-in-cart">🛒 In Cart</a>
                        <?php else: ?>
                            <a href="add_to_cart.php?id=<?php echo $c['id']; ?>" class="btn-add-cart">Add to Cart</a>
                        <?php endif; ?>
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
