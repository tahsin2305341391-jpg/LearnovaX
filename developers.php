<?php
// ============================================
// LearnovaX - Developer Credits Page
// developers.php
// ============================================
require_once 'config/db.php';
require_once 'config/auth.php';

if (isLoggedIn()) redirectByRole(getUserRole());

$activePage = 'developers';

$team = [
    [
        'name'        => 'Alex Johnson',
        'id'          => '221-15-001',
        'role'        => 'Team Lead & Backend Developer',
        'avatar'      => 'dev1.png',
        'github'      => 'alexjohnson',
        'linkedin'    => 'alexjohnson',
        'badge'       => 'Phase 1',
        'badge_color' => 'purple',
        'phase'       => 'Phase 1 — Authentication & Security',
        'phase_icon'  => '🔐',
        'works'       => [
            'Designed the full database schema (6 tables)',
            'Built the secure registration & login system',
            'Implemented role-based access control (Admin, Instructor, Student)',
            'Created session management & auth helper functions',
            'Developed password hashing & input sanitization',
            'Set up XAMPP environment & database connection (PDO)',
        ],
        'tech'        => ['PHP', 'MySQL', 'PDO', 'Sessions', 'HTML', 'CSS'],
        'color'       => '#6c63ff',
        'bg'          => '#ede9fe',
    ],
    [
        'name'        => 'Sarah Williams',
        'id'          => '221-15-002',
        'role'        => 'Full Stack Developer',
        'avatar'      => 'dev2.png',
        'github'      => 'sarahwilliams',
        'linkedin'    => 'sarahwilliams',
        'badge'       => 'Phase 2',
        'badge_color' => 'blue',
        'phase'       => 'Phase 2 — Course Management System',
        'phase_icon'  => '📚',
        'works'       => [
            'Built complete CRUD system for courses',
            'Developed instructor course create/edit/delete pages',
            'Implemented course thumbnail upload with validation',
            'Created admin course approval & rejection system',
            'Built admin user management (activate/deactivate/delete)',
            'Designed instructor & admin dashboards with live statistics',
        ],
        'tech'        => ['PHP', 'MySQL', 'File Upload', 'CSS Flexbox', 'JavaScript'],
        'color'       => '#3b82f6',
        'bg'          => '#dbeafe',
    ],
    [
        'name'        => 'Michael Chen',
        'id'          => '221-15-003',
        'role'        => 'Frontend & Integration Developer',
        'avatar'      => 'dev3.png',
        'github'      => 'michaelchen',
        'linkedin'    => 'michaelchen',
        'badge'       => 'Phase 3',
        'badge_color' => 'green',
        'phase'       => 'Phase 3 — Student Features & UX',
        'phase_icon'  => '🎓',
        'works'       => [
            'Built browse courses page with search & filter system',
            'Developed course detail page with full info display',
            'Implemented shopping cart & simulated checkout flow',
            'Created order history & enrollment tracking system',
            'Built star rating & review submission system',
            'Developed profile management with photo upload',
        ],
        'tech'        => ['PHP', 'MySQL', 'CSS Grid', 'JavaScript', 'DOM Manipulation'],
        'color'       => '#10b981',
        'bg'          => '#d1fae5',
    ],
    [
        'name'        => 'Emily Rodriguez',
        'id'          => '221-15-004',
        'role'        => 'UI/UX Designer & Frontend Developer',
        'avatar'      => 'dev4.png',
        'github'      => 'emilyrodriguez',
        'linkedin'    => 'emilyrodriguez',
        'badge'       => 'Phase 4',
        'badge_color' => 'orange',
        'phase'       => 'Phase 4 — Landing Page & Public UI',
        'phase_icon'  => '🎨',
        'works'       => [
            'Designed & built the full public landing page',
            'Created animated hero section with gradient effects',
            'Developed live statistics counter with scroll animation',
            'Built responsive navigation with mobile hamburger menu',
            'Designed featured courses, categories & testimonial sections',
            'Implemented scroll-reveal animations & CTA sections',
        ],
        'tech'        => ['HTML5', 'CSS3', 'JavaScript', 'Animations', 'Responsive Design'],
        'color'       => '#f59e0b',
        'bg'          => '#fef3c7',
    ],
];

$phases = [
    ['number'=>'01','title'=>'Authentication & Security','icon'=>'🔐','dev'=>'Alex Johnson',    'color'=>'#6c63ff','bg'=>'#ede9fe','items'=>['Database Design','User Registration','Login System','Role-Based Access','Session Management']],
    ['number'=>'02','title'=>'Course Management',        'icon'=>'📚','dev'=>'Sarah Williams',  'color'=>'#3b82f6','bg'=>'#dbeafe','items'=>['Course CRUD','Admin Approval','File Uploads','User Management','Dashboards']],
    ['number'=>'03','title'=>'Student Features',         'icon'=>'🎓','dev'=>'Michael Chen',    'color'=>'#10b981','bg'=>'#d1fae5','items'=>['Browse & Search','Shopping Cart','Checkout Flow','Reviews & Ratings','Profile Management']],
    ['number'=>'04','title'=>'Landing Page & UI',        'icon'=>'🎨','dev'=>'Emily Rodriguez', 'color'=>'#f59e0b','bg'=>'#fef3c7','items'=>['Hero Section','Stats Counter','Course Showcase','Mobile Responsive','Animations']],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meet the Team – LearnovaX</title>
    <link rel="stylesheet" href="assets/css/landing.css">
    <link rel="stylesheet" href="assets/css/developers.css">
</head>
<body>

<?php include 'includes/public_navbar.php'; ?>

<!-- ══════════════════════════════════════════
     DEV HERO
══════════════════════════════════════════ -->
<section class="dev-hero">
    <div class="dev-hero-container">
        <div class="section-badge" style="margin-bottom:16px;">👨‍💻 SE 322 — Spring 2026</div>
        <h1 class="dev-hero-title">
            Meet the <span class="gradient-text">Developers</span>
        </h1>
        <p class="dev-hero-sub">
            LearnovaX was built by a team of 4 passionate Software Engineering students
            at Daffodil International University. Each member owned a complete phase
            of the project from design to deployment.
        </p>
        <div class="dev-hero-meta">
            <div class="dev-meta-item">
                <span class="dev-meta-icon">🏫</span>
                <span>Daffodil International University</span>
            </div>
            <div class="dev-meta-dot">·</div>
            <div class="dev-meta-item">
                <span class="dev-meta-icon">📘</span>
                <span>Dept. of Software Engineering</span>
            </div>
            <div class="dev-meta-dot">·</div>
            <div class="dev-meta-item">
                <span class="dev-meta-icon">📅</span>
                <span>Spring 2026</span>
            </div>
        </div>
    </div>
</section>

<!-- PROJECT BANNER -->
<section class="project-banner">
    <div class="project-banner-container">
        <div class="project-info">
            <div class="project-label">Project</div>
            <div class="project-name">LearnovaX</div>
            <div class="project-tagline">Learn Smart, Grow Fast</div>
        </div>
        <div class="project-divider"></div>
        <div class="project-info">
            <div class="project-label">Course</div>
            <div class="project-name">SE 322</div>
            <div class="project-tagline">Web Applications Lab</div>
        </div>
        <div class="project-divider"></div>
        <div class="project-info">
            <div class="project-label">Category</div>
            <div class="project-name">E-Commerce</div>
            <div class="project-tagline">Multi-Role Platform</div>
        </div>
        <div class="project-divider"></div>
        <div class="project-info">
            <div class="project-label">Tech Stack</div>
            <div class="project-name">PHP + MySQL</div>
            <div class="project-tagline">HTML · CSS · JavaScript</div>
        </div>
    </div>
</section>

<!-- PHASE ROADMAP -->
<section class="section" id="phases">
    <div class="section-container">
        <div class="section-header">
            <div class="section-badge">🗺️ Project Roadmap</div>
            <h2 class="section-title">Work Division by Phase</h2>
            <p class="section-sub">Each developer owned one complete phase of the project</p>
        </div>
        <div class="roadmap">
            <?php foreach ($phases as $i => $phase): ?>
            <div class="roadmap-item">
                <div class="roadmap-line <?php echo $i < count($phases)-1 ? 'show-line':''; ?>"></div>
                <div class="roadmap-dot" style="background:<?php echo $phase['color']; ?>">
                    <?php echo $phase['icon']; ?>
                </div>
                <div class="roadmap-card" style="border-left:4px solid <?php echo $phase['color']; ?>">
                    <div class="roadmap-header">
                        <div>
                            <span class="roadmap-number" style="color:<?php echo $phase['color']; ?>">
                                Phase <?php echo $phase['number']; ?>
                            </span>
                            <h3 class="roadmap-title"><?php echo $phase['title']; ?></h3>
                        </div>
                        <div class="roadmap-dev" style="background:<?php echo $phase['bg']; ?>;color:<?php echo $phase['color']; ?>">
                            👤 <?php echo $phase['dev']; ?>
                        </div>
                    </div>
                    <div class="roadmap-items">
                        <?php foreach ($phase['items'] as $item): ?>
                            <span class="roadmap-tag" style="background:<?php echo $phase['bg']; ?>;color:<?php echo $phase['color']; ?>">
                                <?php echo $item; ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- TEAM CARDS -->
<section class="section section-gray" id="team">
    <div class="section-container">
        <div class="section-header">
            <div class="section-badge">👥 The Team</div>
            <h2 class="section-title">Developer Profiles</h2>
            <p class="section-sub">Get to know the people who built LearnovaX</p>
        </div>
        <div class="team-grid">
            <?php foreach ($team as $i => $member): ?>
            <div class="member-card" id="member-<?php echo $i+1; ?>">
                <div class="member-card-top" style="background:linear-gradient(135deg,<?php echo $member['color']; ?>22,<?php echo $member['color']; ?>11)">
                    <div class="member-phase-badge" style="background:<?php echo $member['color']; ?>">
                        <?php echo $member['badge']; ?>
                    </div>
                    <div class="member-avatar-wrap">
                        <img src="assets/uploads/<?php echo htmlspecialchars($member['avatar']); ?>"
                             onerror="this.style.display='none';this.nextElementSibling.style.display='flex';"
                             class="member-avatar" alt="<?php echo htmlspecialchars($member['name']); ?>">
                        <div class="member-avatar-fallback"
                             style="background:linear-gradient(135deg,<?php echo $member['color']; ?>,<?php echo $member['color']; ?>99);display:none;">
                            <?php echo strtoupper(substr($member['name'],0,1)); ?>
                        </div>
                    </div>
                    <h3 class="member-name"><?php echo htmlspecialchars($member['name']); ?></h3>
                    <p class="member-role" style="color:<?php echo $member['color']; ?>">
                        <?php echo htmlspecialchars($member['role']); ?>
                    </p>
                    <p class="member-id">ID: <?php echo htmlspecialchars($member['id']); ?></p>
                    <div class="member-socials">
                        <a href="https://github.com/<?php echo $member['github']; ?>" target="_blank" class="social-btn github">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/></svg>
                            GitHub
                        </a>
                        <a href="https://linkedin.com/in/<?php echo $member['linkedin']; ?>" target="_blank" class="social-btn linkedin">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                            LinkedIn
                        </a>
                    </div>
                </div>
                <div class="member-card-body">
                    <div class="member-phase-title">
                        <span><?php echo $member['phase_icon']; ?></span>
                        <span><?php echo htmlspecialchars($member['phase']); ?></span>
                    </div>
                    <h4 class="member-works-title">Key Contributions</h4>
                    <ul class="member-works">
                        <?php foreach ($member['works'] as $work): ?>
                        <li>
                            <span class="work-check" style="color:<?php echo $member['color']; ?>">✓</span>
                            <?php echo htmlspecialchars($work); ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="member-tech">
                        <h4 class="member-tech-title">Technologies Used</h4>
                        <div class="tech-tags">
                            <?php foreach ($member['tech'] as $tech): ?>
                            <span class="tech-tag" style="background:<?php echo $member['bg']; ?>;color:<?php echo $member['color']; ?>">
                                <?php echo $tech; ?>
                            </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- TECH STACK -->
<section class="section" id="stack">
    <div class="section-container">
        <div class="section-header">
            <div class="section-badge">⚙️ Technology</div>
            <h2 class="section-title">Tech Stack Used</h2>
            <p class="section-sub">Tools and technologies that power LearnovaX</p>
        </div>
        <div class="stack-grid">
            <div class="stack-card"><div class="stack-icon" style="background:#fff3e0;">🌐</div><div><div class="stack-name">HTML5</div><div class="stack-desc">Semantic markup & form structures</div></div></div>
            <div class="stack-card"><div class="stack-icon" style="background:#e3f2fd;">🎨</div><div><div class="stack-name">CSS3</div><div class="stack-desc">Flexbox, Grid, animations & responsive design</div></div></div>
            <div class="stack-card"><div class="stack-icon" style="background:#fffde7;">⚡</div><div><div class="stack-name">JavaScript</div><div class="stack-desc">DOM manipulation, validation & interactivity</div></div></div>
            <div class="stack-card"><div class="stack-icon" style="background:#f3e5f5;">🐘</div><div><div class="stack-name">PHP 8</div><div class="stack-desc">Server-side logic, OOP & form handling</div></div></div>
            <div class="stack-card"><div class="stack-icon" style="background:#e8f5e9;">🗄️</div><div><div class="stack-name">MySQL</div><div class="stack-desc">Relational database with PDO & prepared statements</div></div></div>
            <div class="stack-card"><div class="stack-icon" style="background:#fce4ec;">🔧</div><div><div class="stack-name">XAMPP</div><div class="stack-desc">Local development environment (Apache + MySQL)</div></div></div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-banner">
    <div class="cta-container">
        <h2 class="cta-title">Want to Experience What We Built?</h2>
        <p class="cta-sub">Register now and explore LearnovaX for free!</p>
        <div class="cta-actions">
            <a href="auth/register.php" class="btn-cta-white">Get Started Free</a>
            <a href="index.php"         class="btn-cta-outline">← Back to Home</a>
        </div>
    </div>
</section>

<?php include 'includes/public_footer.php'; ?>

<script src="assets/js/landing.js"></script>
<script src="assets/js/developers.js"></script>
</body>
</html>
