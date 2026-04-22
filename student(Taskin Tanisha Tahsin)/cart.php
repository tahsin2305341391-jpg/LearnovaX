<?php
// ============================================
// LearnovaX - Shopping Cart
// student/cart.php
// ============================================
require_once '../config/db.php';
require_once '../config/auth.php';
requireRole('student');

$studentId = getUserId();
$flash     = getFlash();

// ── Remove from cart ──────────────────────────
if (isset($_GET['remove'])) {
    $id = (int)$_GET['remove'];
    $conn->prepare("DELETE FROM cart WHERE id=? AND student_id=?")->execute([$id, $studentId]);
    setFlash('success', 'Course removed from cart.');
    header("Location: cart.php"); exit();
}

// ── Checkout (simulate payment) ───────────────
if (isset($_POST['checkout'])) {
    // Get all cart items
    $stmt = $conn->prepare("SELECT course_id FROM cart WHERE student_id=?");
    $stmt->execute([$studentId]);
    $cartItems = $stmt->fetchAll();

    if (empty($cartItems)) {
        setFlash('error', 'Your cart is empty.');
        header("Location: cart.php"); exit();
    }

    // Enroll in each course
    $enrolled = 0;
    foreach ($cartItems as $item) {
        // Skip if already enrolled
        $check = $conn->prepare("SELECT id FROM enrollments WHERE student_id=? AND course_id=?");
        $check->execute([$studentId, $item['course_id']]);
        if ($check->rowCount() === 0) {
            $conn->prepare("INSERT INTO enrollments (student_id, course_id, payment_status) VALUES (?,?,'paid')")
                 ->execute([$studentId, $item['course_id']]);
            $enrolled++;
        }
    }

    // Clear cart
    $conn->prepare("DELETE FROM cart WHERE student_id=?")->execute([$studentId]);

    setFlash('success', "Payment successful! You are now enrolled in $enrolled course(s).");
    header("Location: my_courses.php"); exit();
}

// ── Fetch cart items ──────────────────────────
$stmt = $conn->prepare("
    SELECT cart.id AS cart_id, c.id AS course_id, c.title, c.price,
           c.thumbnail, c.level, u.name AS instructor_name,
           cat.name AS category_name
    FROM cart
    JOIN courses c ON cart.course_id = c.id
    JOIN users u ON c.instructor_id = u.id
    LEFT JOIN categories cat ON c.category_id = cat.id
    WHERE cart.student_id = ?
    ORDER BY cart.added_at DESC
");
$stmt->execute([$studentId]);
$cartItems = $stmt->fetchAll();

// Calculate total
$total = array_sum(array_column($cartItems, 'price'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart – LearnovaX</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/browse.css">
    <link rel="stylesheet" href="../assets/css/cart.css">
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
            <a href="cart.php"       class="nav-item active">🛒 Cart</a>
            <a href="orders.php"     class="nav-item">📋 Order History</a>
            <a href="profile.php"    class="nav-item">👤 Profile</a>
        </nav>
        <div class="sidebar-footer">
            <a href="../auth/logout.php" class="btn-logout">🚪 Logout</a>
        </div>
    </aside>

    <main class="main-content">
        <div class="topbar">
            <h1 class="page-title">🛒 Shopping Cart</h1>
            <div class="user-info">👤 <strong><?php echo getUserName(); ?></strong></div>
        </div>

        <?php if ($flash): ?>
            <div class="alert alert-<?php echo $flash['type']; ?>"><?php echo $flash['message']; ?></div>
        <?php endif; ?>

        <?php if (empty($cartItems)): ?>
            <div class="empty-state">
                <div style="font-size:48px;margin-bottom:16px;">🛒</div>
                <p>Your cart is empty.</p>
                <a href="browse.php" class="btn-primary">Browse Courses</a>
            </div>
        <?php else: ?>

        <div class="cart-layout">
            <!-- Cart Items -->
            <div class="cart-items">
                <?php foreach ($cartItems as $item): ?>
                <div class="cart-item">
                    <img src="../assets/uploads/<?php echo htmlspecialchars($item['thumbnail']); ?>"
                         onerror="this.src='../assets/uploads/default_course.png'"
                         class="cart-thumb" alt="">

                    <div class="cart-item-info">
                        <a href="course_detail.php?id=<?php echo $item['course_id']; ?>" class="cart-item-title">
                            <?php echo htmlspecialchars($item['title']); ?>
                        </a>
                        <p class="cart-item-meta">
                            by <?php echo htmlspecialchars($item['instructor_name']); ?>
                            &nbsp;·&nbsp; <?php echo ucfirst($item['level']); ?>
                            &nbsp;·&nbsp; <?php echo htmlspecialchars($item['category_name'] ?? 'General'); ?>
                        </p>
                    </div>

                    <div class="cart-item-right">
                        <span class="cart-item-price">
                            <?php echo $item['price'] > 0 ? '$' . number_format($item['price'], 2) : 'FREE'; ?>
                        </span>
                        <a href="cart.php?remove=<?php echo $item['cart_id']; ?>"
                           class="cart-remove"
                           onclick="return confirm('Remove this course from cart?')">✕ Remove</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Order Summary -->
            <div class="cart-summary">
                <h2 class="summary-title">Order Summary</h2>

                <div class="summary-rows">
                    <div class="summary-row">
                        <span>Courses (<?php echo count($cartItems); ?>)</span>
                        <span>$<?php echo number_format($total, 2); ?></span>
                    </div>
                    <div class="summary-row total-row">
                        <span>Total</span>
                        <span>$<?php echo number_format($total, 2); ?></span>
                    </div>
                </div>

                <form method="POST" action="cart.php">
                    <button type="submit" name="checkout" class="btn-checkout"
                            onclick="return confirm('Confirm payment of $<?php echo number_format($total, 2); ?>?')">
                        💳 Confirm & Pay $<?php echo number_format($total, 2); ?>
                    </button>
                </form>

                <a href="browse.php" class="btn-secondary" style="display:block;text-align:center;margin-top:12px;">
                    ← Continue Shopping
                </a>

                <p class="secure-note">🔒 Secure simulated checkout</p>
            </div>
        </div>

        <?php endif; ?>
    </main>
</div>
</body>
</html>
