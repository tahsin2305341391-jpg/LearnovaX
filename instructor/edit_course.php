<?php
// ============================================
// LearnovaX - Edit Course
// instructor/edit_course.php
// ============================================
require_once '../config/db.php';
require_once '../config/auth.php';
requireRole('instructor');

$courseId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch course — must belong to this instructor
$stmt = $conn->prepare("SELECT * FROM courses WHERE id = ? AND instructor_id = ?");
$stmt->execute([$courseId, getUserId()]);
$course = $stmt->fetch();

if (!$course) {
    setFlash('error', 'Course not found or access denied.');
    header("Location: dashboard.php");
    exit();
}

$errors = [];
$categories = $conn->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// Pre-fill fields
$title       = $course['title'];
$description = $course['description'];
$price       = $course['price'];
$level       = $course['level'];
$category_id = $course['category_id'];
$thumbnail   = $course['thumbnail'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

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
        $errors[] = "Description is required.";
    elseif (strlen($description) < 20)
        $errors[] = "Description must be at least 20 characters.";

    if ($price === '' || !is_numeric($price) || $price < 0)
        $errors[] = "Please enter a valid price.";

    if (!in_array($level, ['beginner', 'intermediate', 'advanced']))
        $errors[] = "Please select a valid level.";

    if (empty($category_id))
        $errors[] = "Please select a category.";

    // ── Handle new thumbnail upload ───────────
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === 0) {
        $allowed  = ['image/jpeg', 'image/png', 'image/webp'];
        $maxSize  = 2 * 1024 * 1024;
        $fileType = $_FILES['thumbnail']['type'];
        $fileSize = $_FILES['thumbnail']['size'];

        if (!in_array($fileType, $allowed)) {
            $errors[] = "Thumbnail must be JPG, PNG or WEBP.";
        } elseif ($fileSize > $maxSize) {
            $errors[] = "Thumbnail must be under 2MB.";
        } else {
            // Delete old thumbnail if not default
            if ($thumbnail !== 'default_course.png') {
                $oldPath = '../assets/uploads/' . $thumbnail;
                if (file_exists($oldPath)) unlink($oldPath);
            }
            $ext       = pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION);
            $thumbnail = 'course_' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['thumbnail']['tmp_name'], '../assets/uploads/' . $thumbnail);
        }
    }

    // ── Update Course ─────────────────────────
    if (empty($errors)) {
        $stmt = $conn->prepare("
            UPDATE courses
            SET title=?, description=?, price=?, level=?, category_id=?, thumbnail=?, status='pending'
            WHERE id=? AND instructor_id=?
        ");
        $stmt->execute([$title, $description, $price, $level, $category_id, $thumbnail, $courseId, getUserId()]);

        setFlash('success', 'Course updated! Re-submitted for admin approval.');
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
    <title>Edit Course – LearnovaX</title>
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
            <h1 class="page-title">✏️ Edit Course</h1>
            <div class="user-info">👤 <strong><?php echo getUserName(); ?></strong></div>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul><?php foreach ($errors as $e) echo "<li>$e</li>"; ?></ul>
            </div>
        <?php endif; ?>

        <div class="form-card">
            <form action="edit_course.php?id=<?php echo $courseId; ?>" method="POST"
                  enctype="multipart/form-data" id="courseForm" novalidate>

                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="title">Course Title <span class="required">*</span></label>
                        <input type="text" id="title" name="title"
                               value="<?php echo htmlspecialchars($title); ?>" required>
                        <span class="field-error" id="titleErr"></span>
                    </div>

                    <div class="form-group full-width">
                        <label for="description">Description <span class="required">*</span></label>
                        <textarea id="description" name="description" rows="5"><?php echo htmlspecialchars($description); ?></textarea>
                        <span class="field-error" id="descErr"></span>
                    </div>

                    <div class="form-group">
                        <label for="category_id">Category <span class="required">*</span></label>
                        <select id="category_id" name="category_id">
                            <option value="">Select category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"
                                    <?php echo $category_id == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="level">Level <span class="required">*</span></label>
                        <select id="level" name="level">
                            <option value="beginner"     <?php echo $level === 'beginner'     ? 'selected' : ''; ?>>Beginner</option>
                            <option value="intermediate" <?php echo $level === 'intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                            <option value="advanced"     <?php echo $level === 'advanced'     ? 'selected' : ''; ?>>Advanced</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="price">Price (USD) <span class="required">*</span></label>
                        <input type="number" id="price" name="price"
                               min="0" step="0.01"
                               value="<?php echo $price; ?>">
                    </div>

                    <div class="form-group">
                        <label>Current Thumbnail</label>
                        <div class="current-thumb">
                            <img src="../assets/uploads/<?php echo htmlspecialchars($thumbnail); ?>"
                                 onerror="this.src='../assets/uploads/default_course.png'"
                                 alt="Current Thumbnail">
                        </div>
                        <label for="thumbnail">Change Thumbnail</label>
                        <input type="file" id="thumbnail" name="thumbnail" accept="image/*">
                        <small class="hint">Leave empty to keep current thumbnail.</small>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="dashboard.php" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary">Update Course</button>
                </div>

            </form>
        </div>
    </main>
</div>
<script src="../assets/js/course_form.js"></script>
</body>
</html>
