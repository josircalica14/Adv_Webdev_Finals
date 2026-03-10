<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/Showcase/ShowcaseManager.php';
require_once __DIR__ . '/../../includes/Portfolio/PortfolioManager.php';
require_once __DIR__ . '/../../includes/Portfolio/PortfolioRepository.php';
require_once __DIR__ . '/../../includes/Portfolio/PortfolioItemRepository.php';
require_once __DIR__ . '/../../includes/FileStorageManager.php';
require_once __DIR__ . '/../../includes/Auth/UserRepository.php';
require_once __DIR__ . '/../../includes/Customization/CustomizationEngine.php';
require_once __DIR__ . '/../../includes/Customization/CustomizationSettingsRepository.php';

// Get username from URL
$username = $_GET['username'] ?? '';

if (empty($username)) {
    header('Location: index.php');
    exit;
}

// Get database connection
$db = Database::getInstance()->getConnection();
$config = Config::getInstance();

// Get user by username
$userRepo = new UserRepository($db);
$user = $userRepo->findByUsername($username);

if (!$user) {
    http_response_code(404);
    echo "Portfolio not found";
    exit;
}

// Get portfolio
$portfolioRepo = new Portfolio\PortfolioRepository($db);
$portfolio = $portfolioRepo->findByUserId($user->id);

// Get current user
$currentUser = currentUser();

// Check if portfolio exists
if (!$portfolio) {
    http_response_code(404);
    echo "Portfolio not found";
    exit;
}

// Check if portfolio is public OR if the current user is the owner
$isOwner = $currentUser && $currentUser->id === $user->id;
if (!$portfolio->isPublic() && !$isOwner) {
    http_response_code(404);
    echo "Portfolio not found or is private";
    exit;
}

// Get portfolio items
$configArray = [
    'paths' => $config->get('paths'),
    'files' => $config->get('files')
];
$fileManager = new FileStorageManager($db, $configArray);
$portfolioManager = new Portfolio\PortfolioManager($db, $fileManager);
$portfolioItems = $portfolioManager->getItems($user->id);

// Filter only visible items
$visibleItems = array_filter($portfolioItems, fn($item) => $item['is_visible'] ?? true);

// Load customization settings - ONLY for PDF export, NOT for web view
// Public portfolio always uses website theme colors
$portfolioRepoForCustom = new Portfolio\PortfolioRepository($db);
$customizationEngine = new Customization\CustomizationEngine($db, $portfolioRepoForCustom);
$customizationSettings = $customizationEngine->getSettings($user->id);
// Note: customCSS is NOT generated or used for web view

// Separate items by type and extract skills
$projects = [];
$achievements = [];
$milestones = [];
$technicalSkills = [];
$softSkills = [];

// Common technical skills keywords
$technicalKeywords = [
    'JavaScript', 'Python', 'Java', 'C++', 'C#', 'PHP', 'Ruby', 'Swift', 'Kotlin',
    'React', 'Vue', 'Angular', 'Node', 'Express', 'Django', 'Flask', 'Laravel',
    'MySQL', 'PostgreSQL', 'MongoDB', 'Redis', 'Firebase',
    'HTML', 'CSS', 'SASS', 'Tailwind', 'Bootstrap',
    'Git', 'Docker', 'Kubernetes', 'AWS', 'Azure', 'GCP',
    'API', 'REST', 'GraphQL', 'SQL', 'NoSQL',
    'Machine Learning', 'AI', 'Data Science', 'Deep Learning',
    'Mobile Development', 'iOS', 'Android', 'Flutter', 'React Native',
    'Cybersecurity', 'Penetration Testing', 'Ethical Hacking',
    'Game Development', 'Unity', 'Unreal Engine',
    'UI/UX Design', 'Figma', 'Adobe XD', 'Photoshop',
    'Testing', 'Jest', 'Pytest', 'Selenium', 'JUnit'
];

// Common soft skills keywords
$softKeywords = [
    'Leadership', 'Communication', 'Problem Solving', 'Creativity',
    'Critical Thinking', 'Teamwork', 'Team Leadership', 'Collaboration',
    'Time Management', 'Adaptability', 'Work Ethic', 'Attention to Detail',
    'Public Speaking', 'Presentation', 'Writing', 'Research',
    'Project Management', 'Organization', 'Planning', 'Strategic Thinking'
];

foreach ($visibleItems as $item) {
    $itemType = $item['item_type'] ?? '';
    $itemTitle = $item['title'] ?? '';
    
    // Categorize by type
    switch ($itemType) {
        case 'project':
            $projects[] = $item;
            break;
        case 'achievement':
            // Only extract skills if the title contains "Skills" or "Expertise"
            if (stripos($itemTitle, 'Skills') !== false || stripos($itemTitle, 'Expertise') !== false) {
                // Extract and categorize skills from tags
                if (!empty($item['tags'])) {
                    $tags = is_string($item['tags']) ? json_decode($item['tags'], true) : $item['tags'];
                    if (is_array($tags)) {
                        foreach ($tags as $tag) {
                            $isTechnical = false;
                            foreach ($technicalKeywords as $keyword) {
                                if (stripos($tag, $keyword) !== false) {
                                    if (!in_array($tag, $technicalSkills)) {
                                        $technicalSkills[] = $tag;
                                    }
                                    $isTechnical = true;
                                    break;
                                }
                            }
                            if (!$isTechnical) {
                                foreach ($softKeywords as $keyword) {
                                    if (stripos($tag, $keyword) !== false) {
                                        if (!in_array($tag, $softSkills)) {
                                            $softSkills[] = $tag;
                                        }
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                // Regular achievement, not a skills list
                $achievements[] = $item;
            }
            break;
        case 'milestone':
            $milestones[] = $item;
            break;
        case 'skill':
            // Extract and categorize skills from tags
            if (!empty($item['tags'])) {
                $tags = is_string($item['tags']) ? json_decode($item['tags'], true) : $item['tags'];
                if (is_array($tags)) {
                    foreach ($tags as $tag) {
                        $isTechnical = false;
                        foreach ($technicalKeywords as $keyword) {
                            if (stripos($tag, $keyword) !== false) {
                                if (!in_array($tag, $technicalSkills)) {
                                    $technicalSkills[] = $tag;
                                }
                                $isTechnical = true;
                                break;
                            }
                        }
                        if (!$isTechnical) {
                            foreach ($softKeywords as $keyword) {
                                if (stripos($tag, $keyword) !== false) {
                                    if (!in_array($tag, $softSkills)) {
                                        $softSkills[] = $tag;
                                    }
                                    break;
                                }
                            }
                        }
                    }
                }
            }
            break;
    }
}

// Remove duplicates
$technicalSkills = array_unique($technicalSkills);
$softSkills = array_unique($softSkills);

// Get current user for navigation
$currentUser = currentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($user->fullName); ?> - Portfolio</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/showcase.css">
    <link rel="stylesheet" href="../../css/portfolio-view.css">
    <link rel="stylesheet" href="../../css/chatbot.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Public portfolio always uses website theme - no custom CSS applied -->
</head>
<body>
    <div class="wrapper">
        <?php include "../../includes/nav.php"; ?>
        
        <!-- Portfolio Header Section -->
        <div class="portfolio-header-section">
            <!-- Three.js 3D Background Canvas -->
            <div id="three-canvas-container"></div>
            
            <div class="portfolio-profile-container">
                <div class="portfolio-photo">
                    <?php if ($user->profilePhotoPath && file_exists($user->profilePhotoPath)): ?>
                        <img src="<?php echo htmlspecialchars($user->profilePhotoPath); ?>" alt="<?php echo htmlspecialchars($user->fullName); ?>">
                    <?php else: ?>
                        <div class="portfolio-photo-placeholder">
                            <i class="fas fa-user"></i>
                        </div>
                    <?php endif; ?>
                </div>
                
                <h1 class="portfolio-name"><?php echo htmlspecialchars($user->fullName); ?></h1>
                <p class="portfolio-username">@<?php echo htmlspecialchars($user->username); ?></p>
                <div class="portfolio-program-badge"><?php echo htmlspecialchars($user->program); ?></div>
                
                <?php if ($user->bio): ?>
                    <p class="portfolio-bio"><?php echo nl2br(htmlspecialchars($user->bio)); ?></p>
                <?php endif; ?>
                
                <a href="../../index.php#showcase" class="back-to-showcase-btn">
                    <i class="fas fa-arrow-left"></i> Back to Showcase
                </a>
            </div>
        </div>
        
        <!-- Portfolio Items Section -->
        <div class="bowls-section" id="portfolio-items">
            <?php if (empty($visibleItems)): ?>
                <div class="no-items-message">
                    <i class="fas fa-folder-open"></i>
                    <h3>No Portfolio Items Yet</h3>
                    <p>This portfolio doesn't have any items to display.</p>
                </div>
            <?php else: ?>
                
                <!-- Skills Section -->
                <?php if (!empty($technicalSkills) || !empty($softSkills)): ?>
                    <div class="skills-showcase-section">
                        <div class="bowls-header">
                            <h3>SKILLS & EXPERTISE</h3>
                            <p>— TECHNICAL & SOFT SKILLS</p>
                        </div>
                        
                        <div class="skills-container">
                            <?php if (!empty($technicalSkills)): ?>
                                <div class="skills-category">
                                    <h4 class="skills-category-title">
                                        <i class="fas fa-code"></i> Technical Skills
                                    </h4>
                                    <div class="skills-tags-grid">
                                        <?php foreach ($technicalSkills as $skill): ?>
                                            <span class="skill-tag technical-skill"><?php echo htmlspecialchars($skill); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($softSkills)): ?>
                                <div class="skills-category">
                                    <h4 class="skills-category-title">
                                        <i class="fas fa-users"></i> Soft Skills
                                    </h4>
                                    <div class="skills-tags-grid">
                                        <?php foreach ($softSkills as $skill): ?>
                                            <span class="skill-tag soft-skill"><?php echo htmlspecialchars($skill); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Projects Section -->
                <?php if (!empty($projects)): ?>
                    <div class="bowls-header" style="margin-top: 80px;">
                        <h3>PROJECTS</h3>
                        <p>— SHOWCASING WORK</p>
                    </div>
                    
                    <div class="projects-grid">
                        <?php 
                        // Show only projects in Projects section
                        foreach ($projects as $item): 
                            // Check if project has an image file
                            $hasImage = false;
                            if (!empty($item['file_path']) && file_exists($item['file_path'])) {
                                $hasImage = true;
                            }
                        ?>
                            <div class="project-card">
                                <div class="project-image-container">
                                    <?php if ($hasImage): ?>
                                        <img src="<?php echo htmlspecialchars($item['file_path']); ?>" 
                                             alt="<?php echo htmlspecialchars($item['title']); ?>" 
                                             class="project-image">
                                    <?php else: ?>
                                        <div class="project-image-placeholder">
                                            <i class="fas fa-code"></i>
                                            <span><?php echo strtoupper(substr($item['title'], 0, 2)); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="project-type-badge type-<?php echo htmlspecialchars($item['item_type']); ?>">
                                        PROJECT
                                    </div>
                                </div>
                                
                                <div class="project-content">
                                    <h3 class="project-title"><?php echo htmlspecialchars($item['title']); ?></h3>
                                    
                                    <?php if (!empty($item['item_date'])): ?>
                                        <div class="project-date">
                                            <i class="fas fa-calendar"></i>
                                            <?php echo date('F Y', strtotime($item['item_date'])); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <p class="project-description"><?php echo nl2br(htmlspecialchars($item['description'])); ?></p>
                                    
                                    <?php if (!empty($item['tags'])): ?>
                                        <div class="project-tags">
                                            <?php 
                                            $tags = is_string($item['tags']) ? json_decode($item['tags'], true) : $item['tags'];
                                            if (is_array($tags)):
                                                foreach ($tags as $tag): ?>
                                                    <span class="project-tag"><?php echo htmlspecialchars($tag); ?></span>
                                                <?php endforeach;
                                            endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Achievements Section -->
                <?php if (!empty($achievements) || !empty($milestones)): ?>
                    <div class="bowls-header" style="margin-top: 80px;">
                        <h3>ACHIEVEMENTS</h3>
                        <p>— ACCOMPLISHMENTS & MILESTONES</p>
                    </div>
                    
                    <div class="projects-grid">
                        <?php 
                        $achievementItems = array_merge($achievements, $milestones);
                        foreach ($achievementItems as $item): 
                        ?>
                            <div class="project-card">
                                <div class="project-type-badge type-<?php echo htmlspecialchars($item['item_type']); ?>">
                                    <?php echo strtoupper(htmlspecialchars($item['item_type'])); ?>
                                </div>
                                
                                <div class="project-content">
                                    <h3 class="project-title"><?php echo htmlspecialchars($item['title']); ?></h3>
                                    
                                    <?php if (!empty($item['item_date'])): ?>
                                        <div class="project-date">
                                            <i class="fas fa-calendar"></i>
                                            <?php echo date('F Y', strtotime($item['item_date'])); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <p class="project-description"><?php echo nl2br(htmlspecialchars($item['description'])); ?></p>
                                    
                                    <?php if (!empty($item['tags'])): ?>
                                        <div class="project-tags">
                                            <?php 
                                            $tags = is_string($item['tags']) ? json_decode($item['tags'], true) : $item['tags'];
                                            if (is_array($tags)):
                                                foreach ($tags as $tag): ?>
                                                    <span class="project-tag"><?php echo htmlspecialchars($tag); ?></span>
                                                <?php endforeach;
                                            endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include "../../includes/footer.php"; ?>
    
    <script src="../../js/three-background.js"></script>
    <script src="../../js/chatbot.js"></script>
    <script src="../../js/app.js"></script>
</body>
</html>
