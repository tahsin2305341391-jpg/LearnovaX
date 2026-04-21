<?php
// ============================================
// LearnovaX - Create Course
// instructor/create_course.php
// ============================================
require_once '../config/db.php';
require_once '../config/auth.php';
requireRole('instructor');

$errors  = [];
$success = '';
$title = $description = $price = $level = $category_id = '';

// Fetch categories
$categories = $conn->query("SELECT * FROM categories ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Sanitize
    $title       = sanitize($_POST['title'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $price       = sanitize($_POST['price'] ?? '');
    $level       = sanitize($_POST['level'] ?? '');
    $category_id = sanitize($_POST['category_id'] ?? '');

    // ── Validation ────────────────────────────
    if (empty($title))
        $errors[] = "Course title is required.";
    elseif (strlen($title) < 5)
        $errors[] = "Title must be at least 5 characters.";

    if (empty($description))
        $errors[] = "Course description is required.";
    elseif (strlen($description) < 20)
        $errors[] = "Description must be at least 20 characters.";

    if ($price === '' || !is_numeric($price) || $price < 0)
        $errors[] = "Please enter a valid price (0 for free).";

    if (!in_array($level, ['beginner', 'intermediate', 'advanced']))
        $errors[] = "Please select a valid level.";

    if (empty($category_id))
        $errors[] = "Please select a category.";

    // ── Thumbnail Upload ──────────────────────
    $thumbnail = 'default_course.png';
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === 0) {
        $allowed   = ['image/jpeg', 'image/png', 'image/webp'];
        $maxSize   = 2 * 1024 * 1024; // 2MB
        $fileType  = $_FILES['thumbnail']['type'];
        $fileSize  = $_FILES['thumbnail']['size'];

        if (!in_array($fileType, $allowed)) {
            $errors[] = "Thumbnail must be JPG, PNG or WEBP.";
        } elseif ($fileSize > $maxSize) {
            $errors[] = "Thumbnail must be under 2MB.";
        } else {
            $ext       = pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION);
            $thumbnail = 'course_' . uniqid() . '.' . $ext;
            $uploadDir = '../assets/uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            move_uploaded_file($_FILES['thumbnail']['tmp_name'], $uploadDir . $thumbnail);
        }
    }

    // ── Insert Course ─────────────────────────
    if (empty($errors)) {
        $stmt = $conn->prepare("
            INSERT INTO courses (instructor_id, category_id, title, description, price, thumbnail, level, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')
        ");
        $stmt->execute([getUserId(), $category_id, $title, $description, $price, $thumbnail, $level]);

        setFlash('success', 'Course submitted successfully! Waiting for admin approval.');
        header("Location: dashboard.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Course – LearnovaX</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/course_form.css">
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="sidebar-logo">
            <span class="logo-learn">Learnova</span><span class="logo-x">X</span>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php"     class="nav-item">📊 Dashboard</a>
            <a href="create_course.php" class="nav-item active">➕ Create Course</a>
            <a href="my_courses.php"    class="nav-item">📚 My Courses</a>
            <a href="enrollments.php"   class="nav-item">👥 Enrollments</a>
            <a href="profile.php"       class="nav-item">👤 Profile</a>
        </nav>
        <div class="sidebar-footer">
            <a href="../auth/logout.php" class="btn-logout">🚪 Logout</a>
        </div>
    </aside>

    <main class="main-content">
        <div class="topbar">
            <h1 class="page-title">➕ Create New Course</h1>
            <div class="user-info">👤 <strong><?php echo getUserName(); ?></strong></div>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul><?php foreach ($errors as $e) echo "<li>$e</li>"; ?></ul>
            </div>
        <?php endif; ?>

        <div class="form-card">
            <form action="create_course.php" method="POST" enctype="multipart/form-data" id="courseForm" novalidate>

                <div class="form-grid">
                    <!-- Title -->
                    <div class="form-group full-width">
                        <label for="title">Course Title <span class="required">*</span></label>
                        <input type="text" id="title" name="title"
                               placeholder="e.g. Complete Web Development Bootcamp"
                               value="<?php echo $title; ?>" required>
                        <span class="field-error" id="titleErr"></span>
                    </div>

                    <!-- Description -->
                    <div class="form-group full-width">
                        <label for="description">Course Description <span class="required">*</span></label>
                        <textarea id="description" name="description" rows="5"
                                  placeholder="Describe what students will learn..."><?php echo $description; ?></textarea>
                        <span class="field-error" id="descErr"></span>
                    </div>

                    <!-- Category -->
                    <div class="form-group">
                        <label for="category_id">Category <span class="required">*</span></label>
                        <select id="category_id" name="category_id">
                            <option value="">Select a category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"
                                    <?php echo $category_id == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="field-error" id="categoryErr"></span>
                    </div>

                    <!-- Level -->
                    <div class="form-group">
                        <label for="level">Level <span class="required">*</span></label>
                        <select id="level" name="level">
                            <option value="">Select level</option>
                            <option value="beginner"     <?php echo $level === 'beginner'     ? 'selected' : ''; ?>>Beginner</option>
                            <option value="intermediate" <?php echo $level === 'intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                            <option value="advanced"     <?php echo $level === 'advanced'     ? 'selected' : ''; ?>>Advanced</option>
                        </select>
                        <span class="field-error" id="levelErr"></span>
                    </div>

                    <!-- Price -->
                    <div class="form-group">
                        <label for="price">Price (USD) <span class="required">*</span></label>
                        <input type="number" id="price" name="price"
                               placeholder="0 for free" min="0" step="0.01"
                               value="<?php echo $price; ?>">
                        <span class="field-error" id="priceErr"></span>
                    </div>

                    <!-- Thumbnail -->
                    <div class="form-group">
                        <label for="thumbnail">Course Thumbnail</label>
                        <input type="file" id="thumbnail" name="thumbnail" accept="image/*">
                        <small class="hint">JPG, PNG or WEBP. Max 2MB.</small>
                        <div id="imagePreview" class="image-preview" style="display:none;">
                            <img id="previewImg" src="" alt="Preview">
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="dashboard.php" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary">Submit for Approval</button>
                </div>

            </form>
        </div>
    </main>
</div>

<script src="../assets/js/course_form.js"></script>
</body>
</html>
