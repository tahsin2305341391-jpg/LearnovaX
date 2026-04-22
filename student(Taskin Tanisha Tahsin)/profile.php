<?php
// ============================================
// LearnovaX - Profile Page (Student & Instructor)
// student/profile.php  OR  instructor/profile.php
// ============================================
require_once '../config/db.php';
require_once '../config/auth.php';
requireLogin();

$userId = getUserId();
$role   = getUserRole();
$flash  = getFlash();
$errors = [];

// Fetch current user
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name    = sanitize($_POST['name'] ?? '');
    $bio     = sanitize($_POST['bio']  ?? '');
    $newPass = $_POST['new_password']     ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    // Validate name
    if (empty($name))
        $errors[] = "Name is required.";
    elseif (!preg_match("/^[a-zA-Z\s]+$/", $name))
        $errors[] = "Name must contain letters only.";

    // Password change (optional)
    if (!empty($newPass)) {
        if (strlen($newPass) < 6)
            $errors[] = "New password must be at least 6 characters.";
        elseif ($newPass !== $confirm)
            $errors[] = "Passwords do not match.";
    }

    // Handle profile picture upload
    $pic = $user['profile_pic'];
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
        $allowed  = ['image/jpeg', 'image/png', 'image/webp'];
        $fileType = $_FILES['profile_pic']['type'];
        $fileSize = $_FILES['profile_pic']['size'];

        if (!in_array($fileType, $allowed)) {
            $errors[] = "Profile picture must be JPG, PNG or WEBP.";
        } elseif ($fileSize > 1 * 1024 * 1024) {
            $errors[] = "Profile picture must be under 1MB.";
        } else {
            if ($pic !== 'default.png') {
                $old = '../assets/uploads/' . $pic;
                if (file_exists($old)) unlink($old);
            }
            $ext = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
            $pic = 'user_' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['profile_pic']['tmp_name'], '../assets/uploads/' . $pic);
        }
    }

    if (empty($errors)) {
        if (!empty($newPass)) {
            $hashed = password_hash($newPass, PASSWORD_DEFAULT);
            $conn->prepare("UPDATE users SET name=?, bio=?, profile_pic=?, password=? WHERE id=?")
                 ->execute([$name, $bio, $pic, $hashed, $userId]);
        } else {
            $conn->prepare("UPDATE users SET name=?, bio=?, profile_pic=? WHERE id=?")
                 ->execute([$name, $bio, $pic, $userId]);
        }

        // Update session name
        $_SESSION['name'] = $name;
        $_SESSION['pic']  = $pic;

        setFlash('success', 'Profile updated successfully!');
        header("Location: profile.php"); exit();
    }
}

// Sidebar links based on role
$navLinks = $role === 'instructor' ? [
    ['dashboard.php',     '📊 Dashboard'],
    ['create_course.php', '➕ Create Course'],
    ['my_courses.php',    '📚 My Courses'],
    ['enrollments.php',   '👥 Enrollments'],
    ['profile.php',       '👤 Profile'],
] : [
    ['dashboard.php',  '📊 Dashboard'],
    ['browse.php',     '🔍 Browse Courses'],
    ['my_courses.php', '📚 My Courses'],
    ['cart.php',       '🛒 Cart'],
    ['orders.php',     '📋 Order History'],
    ['profile.php',    '👤 Profile'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile – LearnovaX</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/course_form.css">
    <link rel="stylesheet" href="../assets/css/profile.css">
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="sidebar-logo">
            <span class="logo-learn">Learnova</span><span class="logo-x">X</span>
        </div>
        <nav class="sidebar-nav">
            <?php foreach ($navLinks as [$href, $label]): ?>
                <a href="<?php echo $href; ?>"
                   class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === $href ? 'active' : ''; ?>">
                   <?php echo $label; ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="sidebar-footer">
            <a href="../auth/logout.php" class="btn-logout">🚪 Logout</a>
        </div>
    </aside>

    <main class="main-content">
        <div class="topbar">
            <h1 class="page-title">👤 My Profile</h1>
            <div class="user-info">👤 <strong><?php echo getUserName(); ?></strong></div>
        </div>

        <?php if ($flash): ?>
            <div class="alert alert-<?php echo $flash['type']; ?>"><?php echo $flash['message']; ?></div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul><?php foreach ($errors as $e) echo "<li>$e</li>"; ?></ul>
            </div>
        <?php endif; ?>

        <div class="profile-layout">

            <!-- Profile Picture Card -->
            <div class="profile-pic-card">
                <img src="../assets/uploads/<?php echo htmlspecialchars($user['profile_pic']); ?>"
                     onerror="this.src='../assets/uploads/default.png'"
                     class="profile-avatar" id="avatarPreview" alt="Profile">
                <h3><?php echo htmlspecialchars($user['name']); ?></h3>
                <span class="badge badge-approved"><?php echo ucfirst($user['role']); ?></span>
                <p class="profile-email"><?php echo htmlspecialchars($user['email']); ?></p>
                <p class="profile-joined">Joined <?php echo date('M Y', strtotime($user['created_at'])); ?></p>
            </div>

            <!-- Edit Form -->
            <div class="profile-form-card">
                <h2 class="section-title" style="margin-bottom:20px;">Edit Profile</h2>
                <form method="POST" action="profile.php" enctype="multipart/form-data">

                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled
                               style="background:#f3f4f6;cursor:not-allowed;">
                        <small class="hint">Email cannot be changed.</small>
                    </div>

                    <div class="form-group">
                        <label>Bio</label>
                        <textarea name="bio" rows="3"
                                  placeholder="Tell us about yourself..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Profile Picture</label>
                        <input type="file" name="profile_pic" accept="image/*" id="picInput">
                        <small class="hint">JPG, PNG or WEBP. Max 1MB.</small>
                    </div>

                    <hr style="margin:20px 0;border-color:#f0f0f0;">
                    <h3 style="font-size:15px;margin-bottom:16px;">Change Password <small style="color:#6b7280;font-weight:400;">(leave blank to keep current)</small></h3>

                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" placeholder="Min. 6 characters">
                    </div>

                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" placeholder="Re-enter new password">
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">Save Changes</button>
                    </div>

                </form>
            </div>
        </div>
    </main>
</div>

<script>
// Live avatar preview
document.getElementById('picInput')?.addEventListener('change', function () {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = e => document.getElementById('avatarPreview').src = e.target.result;
        reader.readAsDataURL(file);
    }
});
</script>
</body>
</html>
