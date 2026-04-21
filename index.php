<?php
// ============================================
// LearnovaX - Landing Page
// index.php
// ============================================
require_once 'config/db.php';
require_once 'config/auth.php';

if (isLoggedIn()) redirectByRole(getUserRole());

$activePage = 'home';

// Featured courses
$featuredCourses = $conn->query("
    SELECT c.*, u.name AS instructor_name, cat.name AS category_name,
    (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) AS enrolled_count,
    (SELECT COALESCE(AVG(rating),0) FROM reviews WHERE course_id = c.id) AS avg_rating
    FROM courses c
    JOIN users u ON c.instructor_id = u.id
    LEFT JOIN categories cat ON c.category_id = cat.id
    WHERE c.status = 'approved'
    ORDER BY enrolled_count DESC, c.created_at DESC
    LIMIT 6
")->fetchAll();

// Stats
$totalCourses     = $conn->query("SELECT COUNT(*) FROM courses WHERE status='approved'")->fetchColumn();
$totalStudents    = $conn->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn();
$totalInstructors = $conn->query("SELECT COUNT(*) FROM users WHERE role='instructor'")->fetchColumn();
$totalEnrollments = $conn->query("SELECT COUNT(*) FROM enrollments")->fetchColumn();

// Categories
$categories = $conn->query("
    SELECT cat.*, COUNT(c.id) AS course_count
    FROM categories cat
    LEFT JOIN courses c ON c.category_id = cat.id AND c.status = 'approved'
    GROUP BY cat.id
    ORDER BY course_count DESC
    LIMIT 8
")->fetchAll();

// Testimonials
$testimonials = $conn->query("
    SELECT r.*, u.name AS student_name, c.title AS course_title
    FROM reviews r
    JOIN users u ON r.student_id = u.id
    JOIN courses c ON r.course_id = c.id
    WHERE r.comment IS NOT NULL AND r.comment != ''
    ORDER BY r.created_at DESC
    LIMIT 3
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LearnovaX – Learn Smart, Grow Fast</title>
    <link rel="stylesheet" href="assets/css/landing.css">
</head>
<body>

<?php include 'includes/public_navbar.php'; ?>

<!-- ══════════════════════════════════════════
     HERO
══════════════════════════════════════════ -->
<section class="hero" id="home">
    <div class="hero-container">
        <div class="hero-content">
            <div class="hero-badge">🚀 The Future of Online Learning</div>
            <h1 class="hero-title">
                Learn Smart,<br>
                <span class="gradient-text">Grow Fast.</span>
            </h1>
            <p class="hero-subtitle">
                Explore hundreds of expert-led courses, learn at your own pace,
                and unlock your full potential with LearnovaX.
            </p>
            <div class="hero-search">
                <form action="auth/register.php" method="GET" class="search-form">
                    <input type="text" placeholder="What do you want to learn today?" class="hero-search-input">
                    <button type="submit" class="btn-search">Search Courses</button>
                </form>
            </div>
            <div class="hero-tags">
                <span>Popular:</span>
                <?php foreach (array_slice($categories, 0, 4) as $cat): ?>
                    <a href="auth/register.php" class="hero-tag">
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="hero-visual">
            <div class="hero-card floating">
                <div class="hero-card-inner">
                    <div class="hero-card-icon">🎓</div>
                    <div>
                        <div class="hero-card-title">Start Learning Today</div>
                        <div class="hero-card-sub">Join thousands of learners</div>
                    </div>
                </div>
            </div>
            <div class="hero-blob"></div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════
     STATS BANNER
══════════════════════════════════════════ -->
<section class="stats-banner">
    <div class="stats-container">
        <div class="stat-item">
            <span class="stat-num" data-target="<?php echo $totalCourses; ?>">0</span>
            <span class="stat-lbl">Courses Available</span>
        </div>
        <div class="stat-divider"></div>
        <div class="stat-item">
            <span class="stat-num" data-target="<?php echo $totalStudents; ?>">0</span>
            <span class="stat-lbl">Active Students</span>
        </div>
        <div class="stat-divider"></div>
        <div class="stat-item">
            <span class="stat-num" data-target="<?php echo $totalInstructors; ?>">0</span>
            <span class="stat-lbl">Expert Instructors</span>
        </div>
        <div class="stat-divider"></div>
        <div class="stat-item">
            <span class="stat-num" data-target="<?php echo $totalEnrollments; ?>">0</span>
            <span class="stat-lbl">Enrollments Made</span>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════
     FEATURED COURSES
══════════════════════════════════════════ -->
<section class="section" id="courses">
    <div class="section-container">
        <div class="section-header">
            <div class="section-badge">📚 Featured Courses</div>
            <h2 class="section-title">Explore Top Courses</h2>
            <p class="section-sub">Hand-picked courses from our best instructors</p>
        </div>
        <?php if (empty($featuredCourses)): ?>
            <div class="no-courses">
                <p>Courses coming soon! <a href="auth/register.php">Register</a> to be notified.</p>
            </div>
        <?php else: ?>
        <div class="courses-grid">
            <?php foreach ($featuredCourses as $c): ?>
            <div class="course-card">
                <div class="course-thumb-wrap">
                    <img src="assets/uploads/<?php echo htmlspecialchars($c['thumbnail']); ?>"
                         onerror="this.src='assets/uploads/default_course.png'"
                         class="course-thumb" alt="<?php echo htmlspecialchars($c['title']); ?>">
                    <span class="course-level level-<?php echo $c['level']; ?>">
                        <?php echo ucfirst($c['level']); ?>
                    </span>
                </div>
                <div class="course-body">
                    <span class="course-cat"><?php echo htmlspecialchars($c['category_name'] ?? 'General'); ?></span>
                    <h3 class="course-name"><?php echo htmlspecialchars($c['title']); ?></h3>
                    <p class="course-by">by <?php echo htmlspecialchars($c['instructor_name']); ?></p>
                    <div class="course-meta">
                        <span>⭐ <?php echo $c['avg_rating'] > 0 ? number_format($c['avg_rating'],1) : 'New'; ?></span>
                        <span>👥 <?php echo $c['enrolled_count']; ?></span>
                    </div>
                    <div class="course-footer">
                        <span class="course-price">
                            <?php echo $c['price'] > 0 ? '$'.number_format($c['price'],2) : '<span class="free-tag">FREE</span>'; ?>
                        </span>
                        <a href="auth/register.php" class="btn-enroll">Enroll Now</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="section-cta">
            <a href="auth/register.php" class="btn-outline-purple">View All Courses →</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- ══════════════════════════════════════════
     CATEGORIES
══════════════════════════════════════════ -->
<section class="section section-gray" id="categories">
    <div class="section-container">
        <div class="section-header">
            <div class="section-badge">🗂️ Browse by Topic</div>
            <h2 class="section-title">Explore Categories</h2>
            <p class="section-sub">Find the right course for your career</p>
        </div>
        <div class="categories-grid">
            <?php
            $catIcons = [
                'Web Development'         => '🌐',
                'Mobile Development'      => '📱',
                'Data Science'            => '📊',
                'UI/UX Design'            => '🎨',
                'Cybersecurity'           => '🔒',
                'Cloud Computing'         => '☁️',
                'Artificial Intelligence' => '🤖',
                'Digital Marketing'       => '📣',
            ];
            foreach ($categories as $cat):
                $icon = $catIcons[$cat['name']] ?? '📂';
            ?>
            <a href="auth/register.php" class="category-card">
                <span class="cat-icon"><?php echo $icon; ?></span>
                <span class="cat-name"><?php echo htmlspecialchars($cat['name']); ?></span>
                <span class="cat-count"><?php echo $cat['course_count']; ?> course<?php echo $cat['course_count'] != 1 ? 's' : ''; ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════
     HOW IT WORKS
══════════════════════════════════════════ -->
<section class="section" id="how">
    <div class="section-container">
        <div class="section-header">
            <div class="section-badge">⚡ Simple Process</div>
            <h2 class="section-title">How LearnovaX Works</h2>
            <p class="section-sub">Start your learning journey in 3 easy steps</p>
        </div>
        <div class="steps-grid">
            <div class="step-card">
                <div class="step-number">01</div>
                <div class="step-icon">📝</div>
                <h3>Create an Account</h3>
                <p>Sign up as a student or instructor in seconds. It's completely free to register.</p>
            </div>
            <div class="step-arrow">→</div>
            <div class="step-card">
                <div class="step-number">02</div>
                <div class="step-icon">🔍</div>
                <h3>Explore & Enroll</h3>
                <p>Browse hundreds of courses by category, level, or keyword. Enroll in what interests you.</p>
            </div>
            <div class="step-arrow">→</div>
            <div class="step-card">
                <div class="step-number">03</div>
                <div class="step-icon">🚀</div>
                <h3>Learn & Grow</h3>
                <p>Access your courses anytime. Track your progress and rate your experience.</p>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════
     ABOUT
══════════════════════════════════════════ -->
<section class="section section-gray" id="about">
    <div class="section-container">
        <div class="about-layout">
            <div class="about-content">
                <div class="section-badge">💡 Why LearnovaX</div>
                <h2 class="section-title" style="text-align:left;">Built for the Modern Learner</h2>
                <p class="about-desc">
                    LearnovaX is a next-generation educational marketplace connecting passionate
                    learners with expert instructors. We believe quality education should be
                    accessible, structured, and rewarding for everyone.
                </p>
                <div class="about-features">
                    <div class="about-feature">
                        <span class="feature-icon">✅</span>
                        <div>
                            <strong>Expert-Verified Courses</strong>
                            <p>Every course is reviewed and approved by our admin team before going live.</p>
                        </div>
                    </div>
                    <div class="about-feature">
                        <span class="feature-icon">🔒</span>
                        <div>
                            <strong>Secure & Reliable</strong>
                            <p>Your data and payments are always safe with our secure platform.</p>
                        </div>
                    </div>
                    <div class="about-feature">
                        <span class="feature-icon">🎯</span>
                        <div>
                            <strong>Role-Based Access</strong>
                            <p>Tailored dashboards for students, instructors, and admins.</p>
                        </div>
                    </div>
                    <div class="about-feature">
                        <span class="feature-icon">⭐</span>
                        <div>
                            <strong>Honest Reviews</strong>
                            <p>Only enrolled students can rate courses — real feedback guaranteed.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="about-visual">
                <div class="about-card-stack">
                    <div class="about-stat-card purple">
                        <span class="about-stat-icon">🎓</span>
                        <div>
                            <div class="about-stat-num"><?php echo $totalStudents; ?>+</div>
                            <div class="about-stat-lbl">Students Learning</div>
                        </div>
                    </div>
                    <div class="about-stat-card blue">
                        <span class="about-stat-icon">📚</span>
                        <div>
                            <div class="about-stat-num"><?php echo $totalCourses; ?>+</div>
                            <div class="about-stat-lbl">Courses Available</div>
                        </div>
                    </div>
                    <div class="about-stat-card green">
                        <span class="about-stat-icon">🧑‍🏫</span>
                        <div>
                            <div class="about-stat-num"><?php echo $totalInstructors; ?>+</div>
                            <div class="about-stat-lbl">Expert Instructors</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════
     TESTIMONIALS
══════════════════════════════════════════ -->
<?php if (!empty($testimonials)): ?>
<section class="section" id="testimonials">
    <div class="section-container">
        <div class="section-header">
            <div class="section-badge">💬 Student Reviews</div>
            <h2 class="section-title">What Our Students Say</h2>
            <p class="section-sub">Real reviews from real enrolled students</p>
        </div>
        <div class="testimonials-grid">
            <?php foreach ($testimonials as $t): ?>
            <div class="testimonial-card">
                <div class="testimonial-stars">
                    <?php echo str_repeat('★', $t['rating']) . str_repeat('☆', 5 - $t['rating']); ?>
                </div>
                <p class="testimonial-text">"<?php echo htmlspecialchars($t['comment']); ?>"</p>
                <div class="testimonial-author">
                    <div class="author-avatar">
                        <?php echo strtoupper(substr($t['student_name'], 0, 1)); ?>
                    </div>
                    <div>
                        <strong><?php echo htmlspecialchars($t['student_name']); ?></strong>
                        <span>on <?php echo htmlspecialchars($t['course_title']); ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ══════════════════════════════════════════
     MEET THE TEAM TEASER
══════════════════════════════════════════ -->
<section class="section section-gray" id="team-teaser">
    <div class="section-container">
        <div class="section-header">
            <div class="section-badge">👨‍💻 The Developers</div>
            <h2 class="section-title">Meet the Team Behind LearnovaX</h2>
            <p class="section-sub">4 Software Engineering students who built this platform from scratch</p>
        </div>
        <div class="team-teaser-grid">
            <?php
            $teaserTeam = [
                ['name'=>'Alex Johnson',    'role'=>'Team Lead & Backend Dev',      'phase'=>'Phase 1', 'color'=>'#6c63ff', 'bg'=>'#ede9fe', 'avatar'=>'dev1.png'],
                ['name'=>'Sarah Williams',  'role'=>'Full Stack Developer',          'phase'=>'Phase 2', 'color'=>'#3b82f6', 'bg'=>'#dbeafe', 'avatar'=>'dev2.png'],
                ['name'=>'Michael Chen',    'role'=>'Frontend & Integration Dev',    'phase'=>'Phase 3', 'color'=>'#10b981', 'bg'=>'#d1fae5', 'avatar'=>'dev3.png'],
                ['name'=>'Emily Rodriguez', 'role'=>'UI/UX Designer & Frontend Dev', 'phase'=>'Phase 4', 'color'=>'#f59e0b', 'bg'=>'#fef3c7', 'avatar'=>'dev4.png'],
            ];
            foreach ($teaserTeam as $m): ?>
            <div class="teaser-card">
                <div class="teaser-top" style="background:linear-gradient(135deg,<?php echo $m['color']; ?>22,<?php echo $m['color']; ?>11)">
                    <span class="teaser-phase" style="background:<?php echo $m['color']; ?>"><?php echo $m['phase']; ?></span>
                    <img src="assets/uploads/<?php echo $m['avatar']; ?>"
                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex';"
                         class="teaser-avatar" alt="<?php echo $m['name']; ?>">
                    <div class="teaser-avatar-fallback"
                         style="background:linear-gradient(135deg,<?php echo $m['color']; ?>,<?php echo $m['color']; ?>99);display:none;">
                        <?php echo strtoupper(substr($m['name'],0,1)); ?>
                    </div>
                </div>
                <div class="teaser-body">
                    <h4><?php echo $m['name']; ?></h4>
                    <p style="color:<?php echo $m['color']; ?>"><?php echo $m['role']; ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="section-cta">
            <a href="developers.php" class="btn-outline-purple">View Full Developer Profiles →</a>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════
     CTA BANNER
══════════════════════════════════════════ -->
<section class="cta-banner">
    <div class="cta-container">
        <h2 class="cta-title">Ready to Start Your Learning Journey?</h2>
        <p class="cta-sub">Join LearnovaX today — it's free to register!</p>
        <div class="cta-actions">
            <a href="auth/register.php?role=student"    class="btn-cta-white">Start Learning Free</a>
            <a href="auth/register.php?role=instructor" class="btn-cta-outline">Become an Instructor</a>
        </div>
    </div>
</section>

<?php include 'includes/public_footer.php'; ?>

<script src="assets/js/landing.js"></script>
</body>
</html>
