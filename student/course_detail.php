<?php
// ============================================
// LearnovaX - Course Detail
// student/course_detail.php
// ============================================
require_once '../config/db.php';
require_once '../config/auth.php';
requireRole('student');

$courseId  = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$studentId = getUserId();
$flash     = getFlash();

// Fetch course
$stmt = $conn->prepare("
    SELECT c.*, u.name AS instructor_name, u.bio AS instructor_bio,
           cat.name AS category_name,
    (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) AS enrolled_count,
    (SELECT COALESCE(AVG(rating),0) FROM reviews WHERE course_id = c.id) AS avg_rating,
    (SELECT COUNT(*) FROM reviews WHERE course_id = c.id) AS review_count,
    (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id AND student_id = ?) AS is_enrolled,
    (SELECT COUNT(*) FROM cart WHERE course_id = c.id AND student_id = ?) AS in_cart
    FROM courses c
    JOIN users u ON c.instructor_id = u.id
    LEFT JOIN categories cat ON c.category_id = cat.id
    WHERE c.id = ? AND c.status = 'approved'
");
$stmt->execute([$studentId, $studentId, $courseId]);
$course = $stmt->fetch();

if (!$course) {
    setFlash('error', 'Course not found.');
    header("Location: browse.php"); exit();
}

// Fetch reviews
$reviewStmt = $conn->prepare("
    SELECT r.*, u.name AS student_name
    FROM reviews r
    JOIN users u ON r.student_id = u.id
    WHERE r.course_id = ?
    ORDER BY r.created_at DESC
");
$reviewStmt->execute([$courseId]);
$reviews = $reviewStmt->fetchAll();

// Check if student already reviewed
$hasReviewed = $conn->prepare("SELECT id FROM reviews WHERE student_id=? AND course_id=?");
$hasReviewed->execute([$studentId, $courseId]);
$alreadyReviewed = $hasReviewed->rowCount() > 0;

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!$course['is_enrolled']) {
        setFlash('error', 'You must be enrolled to leave a review.');
    } elseif ($alreadyReviewed) {
        setFlash('error', 'You have already reviewed this course.');
    } else {
        $rating  = (int)($_POST['rating'] ?? 0);
        $comment = sanitize($_POST['comment'] ?? '');
        if ($rating < 1 || $rating > 5) {
            setFlash('error', 'Please select a rating between 1 and 5.');
        } else {
            $stmt = $conn->prepare("INSERT INTO reviews (student_id, course_id, rating, comment) VALUES (?,?,?,?)");
            $stmt->execute([$studentId, $courseId, $rating, $comment]);
            setFlash('success', 'Review submitted! Thank you.');
            header("Location: course_detail.php?id=$courseId"); exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course['title']); ?> – LearnovaX</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/browse.css">
    <link rel="stylesheet" href="../assets/css/course_detail.css">
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
        <?php if ($flash): ?>
            <div class="alert alert-<?php echo $flash['type']; ?>" style="margin-top:20px;">
                <?php echo $flash['message']; ?>
            </div>
        <?php endif; ?>

        <div class="detail-hero">
            <div class="detail-info">
                <div class="detail-meta">
                    <span class="card-category"><?php echo htmlspecialchars($course['category_name'] ?? 'General'); ?></span>
                    <span class="card-level level-<?php echo $course['level']; ?>"><?php echo ucfirst($course['level']); ?></span>
                </div>
                <h1 class="detail-title"><?php echo htmlspecialchars($course['title']); ?></h1>
                <p class="detail-instructor">By <strong><?php echo htmlspecialchars($course['instructor_name']); ?></strong></p>

                <div class="detail-stats">
                    <span>⭐ <?php echo $course['avg_rating'] > 0 ? number_format($course['avg_rating'],1) : 'No ratings yet'; ?></span>
                    <span>👥 <?php echo $course['enrolled_count']; ?> students</span>
                    <span>💬 <?php echo $course['review_count']; ?> reviews</span>
                </div>
            </div>

            <div class="detail-action-card">
                <img src="../assets/uploads/<?php echo htmlspecialchars($course['thumbnail']); ?>"
                     onerror="this.src='../assets/uploads/default_course.png'"
                     class="detail-thumb" alt="">

                <div class="detail-price">
                    <?php echo $course['price'] > 0 ? '$' . number_format($course['price'], 2) : '<span class="free-badge">FREE</span>'; ?>
                </div>

                <?php if ($course['is_enrolled']): ?>
                    <div class="btn-enrolled-full">✅ You are enrolled in this course</div>
                <?php elseif ($course['in_cart']): ?>
                    <a href="cart.php" class="btn-primary" style="display:block;text-align:center;">🛒 Go to Cart</a>
                <?php else: ?>
                    <a href="add_to_cart.php?id=<?php echo $courseId; ?>"
                       class="btn-primary" style="display:block;text-align:center;">Add to Cart</a>
                <?php endif; ?>

                <a href="browse.php" class="btn-secondary" style="display:block;text-align:center;margin-top:10px;">← Back to Browse</a>
            </div>
        </div>

        <!-- Description -->
        <div class="detail-section">
            <h2>About This Course</h2>
            <p><?php echo nl2br(htmlspecialchars($course['description'])); ?></p>
        </div>

        <!-- Instructor -->
        <div class="detail-section">
            <h2>About the Instructor</h2>
            <p><strong><?php echo htmlspecialchars($course['instructor_name']); ?></strong></p>
            <p><?php echo $course['instructor_bio'] ? nl2br(htmlspecialchars($course['instructor_bio'])) : 'No bio available.'; ?></p>
        </div>

        <!-- Reviews -->
        <div class="detail-section">
            <h2>Student Reviews (<?php echo $course['review_count']; ?>)</h2>

            <!-- Submit Review -->
            <?php if ($course['is_enrolled'] && !$alreadyReviewed): ?>
            <div class="review-form-box">
                <h3>Leave a Review</h3>
                <form method="POST" action="course_detail.php?id=<?php echo $courseId; ?>">
                    <div class="star-rating" id="starRating">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>">
                            <label for="star<?php echo $i; ?>">★</label>
                        <?php endfor; ?>
                    </div>
                    <textarea name="comment" placeholder="Share your experience..." rows="4"></textarea>
                    <button type="submit" name="submit_review" class="btn-primary">Submit Review</button>
                </form>
            </div>
            <?php elseif ($alreadyReviewed): ?>
                <p class="already-reviewed">✅ You have already reviewed this course.</p>
            <?php elseif (!$course['is_enrolled']): ?>
                <p class="enroll-to-review">Enroll in this course to leave a review.</p>
            <?php endif; ?>

            <!-- Reviews List -->
            <?php if (empty($reviews)): ?>
                <p style="color:#6b7280;margin-top:16px;">No reviews yet. Be the first!</p>
            <?php else: ?>
                <div class="reviews-list">
                    <?php foreach ($reviews as $r): ?>
                    <div class="review-item">
                        <div class="review-header">
                            <strong><?php echo htmlspecialchars($r['student_name']); ?></strong>
                            <span class="review-stars"><?php echo str_repeat('★', $r['rating']) . str_repeat('☆', 5 - $r['rating']); ?></span>
                            <span class="review-date"><?php echo date('d M Y', strtotime($r['created_at'])); ?></span>
                        </div>
                        <?php if ($r['comment']): ?>
                            <p class="review-comment"><?php echo nl2br(htmlspecialchars($r['comment'])); ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </main>
</div>
</body>
</html>
