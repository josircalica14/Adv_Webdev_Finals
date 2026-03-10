<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Use real authentication system
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/Showcase/ShowcaseManager.php';
require_once __DIR__ . '/includes/Portfolio/PortfolioManager.php';
require_once __DIR__ . '/includes/Cache/CacheManager.php';

// Get current user
$currentUser = currentUser();

// Get database connection
$db = Database::getInstance()->getConnection();
$cacheDir = __DIR__ . '/cache';
$cache = new Cache\CacheManager($cacheDir);

// Get public portfolios from database
$showcaseManager = new Showcase\ShowcaseManager($db, $cache);
$portfoliosResult = $showcaseManager->getPublicPortfolios(1, 20);
$portfolios = [];

// Convert to array format for display
foreach ($portfoliosResult['items'] as $portfolio) {
    $user = $portfolio['user'] ?? [];
    $portfolios[] = [
        'id' => $user['id'] ?? 0,
        'email' => '', // Not included in showcase for privacy
        'full_name' => $user['full_name'] ?? '',
        'username' => $user['username'] ?? '',
        'program' => $user['program'] ?? 'BSIT',
        'bio' => $user['bio'] ?? '',
        'profile_photo' => $user['profile_photo_path'] ?? null,
        'is_public' => true
    ];
}

// Get recent projects from all public portfolios
$projectsQuery = $db->prepare("
    SELECT pi.*, u.full_name, u.username, u.program
    FROM portfolio_items pi
    JOIN portfolios p ON pi.portfolio_id = p.id
    JOIN users u ON p.user_id = u.id
    WHERE p.is_public = 1 
    AND pi.is_visible = 1 
    AND pi.item_type = 'project'
    ORDER BY pi.created_at DESC
    LIMIT 12
");
$projectsQuery->execute();
$recentProjects = $projectsQuery->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include "includes/header.php"; ?>
<link rel="stylesheet" href="css/showcase.css">

<body>

 <div class="wrapper">
     
<?php include "includes/nav.php"; ?>

<div class="hero">
    <!-- Three.js 3D Background Canvas -->
    <div id="three-canvas-container"></div>
    
    <div class="hero-content">
        <h1>PORTFOLIO KO HEHE</h1>
        <h2>STUDENT SHOWCASE PLATFORM</h2>
        <p>
            Discover and share amazing projects from BSIT and CSE students. 
            Create your portfolio, customize it, and showcase your achievements.
        </p>
        <?php if (!$currentUser): ?>
            <a href="pages/auth/register.php" class="hero-cta-btn">Create Your Portfolio</a>
        <?php else: ?>
            <a href="pages/dashboard/dashboard.php" class="hero-cta-btn">Go to Dashboard</a>
        <?php endif; ?>
    </div>
</div>

<!-- Featured Portfolios Section -->
<section class="featured-section" id="featured">
    <div class="featured-header">
        <h3>FEATURED PORTFOLIOS</h3>
        <p>— TOP STUDENT WORK</p>
    </div>
    
    <div class="featured-grid">
        <?php 
        // Get top 3 featured portfolios
        $featuredCount = min(3, count($portfolios));
        for ($i = 0; $i < $featuredCount; $i++): 
            $user = $portfolios[$i];
        ?>
            <div class="featured-card">
                <div class="featured-card-image">
                    <?php if (isset($user['profile_photo']) && $user['profile_photo'] && file_exists($user['profile_photo'])): ?>
                        <img src="<?php echo htmlspecialchars($user['profile_photo']); ?>" alt="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>">
                    <?php else: ?>
                        <div class="featured-placeholder">
                            <i class="fas fa-user"></i>
                        </div>
                    <?php endif; ?>
                    <span class="featured-badge">
                        <i class="fas fa-star"></i> Featured
                    </span>
                </div>
                <div class="featured-card-content">
                    <span class="program-tag <?php echo strtolower($user['program'] ?? 'bsit'); ?>">
                        <?php echo htmlspecialchars($user['program'] ?? 'BSIT'); ?>
                    </span>
                    <h4><?php echo htmlspecialchars($user['full_name'] ?? ''); ?></h4>
                    <p class="featured-bio"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></p>
                    <a href="pages/portfolio/view.php?username=<?php echo urlencode($user['username'] ?? ''); ?>" class="btn-featured">
                        View Portfolio <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        <?php endfor; ?>
    </div>
</section>

<!-- Student Showcase Section -->
<section class="student-showcase-section" id="showcase">
    <div class="showcase-header">
        <h3>STUDENT PORTFOLIOS</h3>
        <p>— DISCOVER TALENTED STUDENTS</p>
    </div>
    
    <!-- Search and Filter -->
    <div class="showcase-filters">
        <div class="search-bar">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Search students...">
        </div>
        
        <div class="filter-controls">
            <button class="filter-btn active" data-filter="all">
                <i class="fas fa-users"></i> All
            </button>
            <button class="filter-btn" data-filter="BSIT">
                <i class="fas fa-laptop-code"></i> BSIT
            </button>
            <button class="filter-btn" data-filter="CSE">
                <i class="fas fa-microchip"></i> CSE
            </button>
        </div>
        
        <div class="skill-filters">
            <label>Filter by Skills:</label>
            <div class="skill-tags">
                <button class="skill-tag" data-skill="JavaScript">JavaScript</button>
                <button class="skill-tag" data-skill="Python">Python</button>
                <button class="skill-tag" data-skill="React">React</button>
                <button class="skill-tag" data-skill="Machine Learning">Machine Learning</button>
                <button class="skill-tag" data-skill="Mobile Development">Mobile Development</button>
                <button class="skill-tag" data-skill="Cybersecurity">Cybersecurity</button>
                <button class="skill-tag" data-skill="Game Development">Game Development</button>
            </div>
        </div>
        
        <div class="soft-skill-filters">
            <label>Filter by Soft Skills:</label>
            <div class="skill-tags">
                <button class="skill-tag" data-soft-skill="Leadership">Leadership</button>
                <button class="skill-tag" data-soft-skill="Communication">Communication</button>
                <button class="skill-tag" data-soft-skill="Problem Solving">Problem Solving</button>
                <button class="skill-tag" data-soft-skill="Creativity">Creativity</button>
                <button class="skill-tag" data-soft-skill="Critical Thinking">Critical Thinking</button>
            </div>
        </div>
    </div>
    
    <!-- Portfolio Grid -->
    <div class="student-portfolio-grid" id="portfolioGrid">
        <?php foreach ($portfolios as $user): ?>
            <div class="student-card" data-program="<?php echo $user['program'] ?? ''; ?>">
                <div class="student-card-header">
                    <div class="student-photo">
                        <?php if (isset($user['profile_photo']) && $user['profile_photo'] && file_exists($user['profile_photo'])): ?>
                            <img src="<?php echo htmlspecialchars($user['profile_photo']); ?>" alt="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>">
                        <?php else: ?>
                            <div class="photo-placeholder">
                                <i class="fas fa-user"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <span class="program-badge <?php echo strtolower($user['program'] ?? 'bsit'); ?>">
                        <?php echo htmlspecialchars($user['program'] ?? 'BSIT'); ?>
                    </span>
                </div>
                
                <div class="student-card-body">
                    <h4><?php echo htmlspecialchars($user['full_name'] ?? ''); ?></h4>
                    <p class="student-username">@<?php echo htmlspecialchars($user['username'] ?? ''); ?></p>
                    <p class="student-bio"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></p>
                    
                    <div class="student-stats">
                        <span><i class="fas fa-briefcase"></i> Portfolio</span>
                    </div>
                </div>
                
                <div class="student-card-footer">
                    <a href="pages/portfolio/view.php?username=<?php echo urlencode($user['username'] ?? ''); ?>" class="btn-view">
                        View Portfolio <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="showcase-footer-cta">
        <p>Want to showcase your work?</p>
        <?php if (!$currentUser): ?>
            <a href="pages/auth/register.php" class="btn-cta">Create Your Portfolio</a>
        <?php else: ?>
            <a href="pages/dashboard/dashboard.php" class="btn-cta">Manage Your Portfolio</a>
        <?php endif; ?>
    </div>
</section>

<!-- Skills Section -->
<div class="skills-section" id="skills">
    <div class="skills-header">
        <h3>SKILLS & EXPERTISE</h3>
        <p>— MY TECHNICAL CAPABILITIES</p>
    </div>
    <!-- Skills will be dynamically rendered here by skills.js -->
</div>

<div class="bowls-section" id="projects">
    <div class="bowls-header">
        <h3>RECENT PROJECTS</h3>
        <p>— STUDENT WORK SHOWCASE</p>
    </div>

    <!-- Projects Grid -->
    <div class="projects-grid">
        <?php if (!empty($recentProjects)): ?>
            <?php foreach ($recentProjects as $project): ?>
                <div class="project-card">
                    <div class="project-image-container">
                        <?php 
                        // Check if project has an image file
                        $hasImage = false;
                        if (!empty($project['file_path']) && file_exists($project['file_path'])) {
                            $hasImage = true;
                        }
                        ?>
                        
                        <?php if ($hasImage): ?>
                            <img src="<?php echo htmlspecialchars($project['file_path']); ?>" 
                                 alt="<?php echo htmlspecialchars($project['title']); ?>" 
                                 class="project-image">
                        <?php else: ?>
                            <div class="project-image-placeholder">
                                <i class="fas fa-code"></i>
                                <span><?php echo strtoupper(substr($project['title'], 0, 2)); ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="project-type-badge type-project">
                            <?php echo strtoupper(htmlspecialchars($project['program'])); ?>
                        </div>
                    </div>
                    
                    <div class="project-content">
                        <h3 class="project-title"><?php echo htmlspecialchars($project['title']); ?></h3>
                        
                        <p class="project-author">
                            <i class="fas fa-user"></i> by <?php echo htmlspecialchars($project['full_name']); ?>
                        </p>
                        
                        <?php if (!empty($project['item_date'])): ?>
                            <div class="project-date">
                                <i class="fas fa-calendar"></i>
                                <?php echo date('F Y', strtotime($project['item_date'])); ?>
                            </div>
                        <?php endif; ?>
                        
                        <p class="project-description">
                            <?php 
                            $desc = htmlspecialchars($project['description']);
                            echo strlen($desc) > 150 ? substr($desc, 0, 150) . '...' : $desc;
                            ?>
                        </p>
                        
                        <?php if (!empty($project['tags'])): ?>
                            <div class="project-tags">
                                <?php 
                                $tags = json_decode($project['tags'], true);
                                if (is_array($tags)):
                                    $displayTags = array_slice($tags, 0, 4);
                                    foreach ($displayTags as $tag): ?>
                                        <span class="project-tag"><?php echo htmlspecialchars($tag); ?></span>
                                    <?php endforeach;
                                    if (count($tags) > 4): ?>
                                        <span class="project-tag">+<?php echo count($tags) - 4; ?> more</span>
                                    <?php endif;
                                endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <a href="pages/portfolio/view.php?username=<?php echo urlencode($project['username']); ?>" class="project-view-link">
                            View Portfolio <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-projects-message">
                <i class="fas fa-folder-open"></i>
                <p>No projects available yet. Be the first to showcase your work!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

</div>

<?php include "includes/footer.php"; ?>

<script src="js/showcase.js"></script>

</body>
</html>