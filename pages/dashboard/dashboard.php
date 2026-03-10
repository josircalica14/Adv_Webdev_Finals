<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Use real authentication system
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/Portfolio/PortfolioManager.php';
require_once __DIR__ . '/../../includes/Portfolio/PortfolioRepository.php';
require_once __DIR__ . '/../../includes/Portfolio/PortfolioItemRepository.php';
require_once __DIR__ . '/../../includes/FileStorageManager.php';

// Check if user is logged in
$currentUser = requireAuth('login.php');

// Get database connection and dependencies
$db = Database::getInstance()->getConnection();
$config = Config::getInstance();
$configArray = [
    'paths' => $config->get('paths'),
    'files' => $config->get('files')
];
$fileManager = new FileStorageManager($db, $configArray);

// Get portfolio items from database
$portfolioManager = new Portfolio\PortfolioManager($db, $fileManager);
$portfolioItems = $portfolioManager->getItems($currentUser->id);
?>
<?php include "../../includes/header.php"; ?>
<link rel="stylesheet" href="../../css/dashboard.css?v=4">
<link rel="stylesheet" href="../../css/forms.css">

<body>

<div class="wrapper">
     
<?php include "../../includes/nav-dashboard.php"; ?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1>Welcome, <?php echo htmlspecialchars($currentUser->fullName); ?>!</h1>
        <p>Manage your portfolio and showcase your work</p>
    </div>
    
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>
    
    <div class="stats-grid">
        <div class="stat-card">
            <i class="fas fa-project-diagram"></i>
            <h3><?php echo count(array_filter($portfolioItems, fn($item) => ($item['item_type'] ?? '') === 'project')); ?></h3>
            <p>Projects</p>
        </div>
        
        <div class="stat-card">
            <i class="fas fa-trophy"></i>
            <h3><?php echo count(array_filter($portfolioItems, fn($item) => ($item['item_type'] ?? '') === 'achievement')); ?></h3>
            <p>Achievements</p>
        </div>
        
        <div class="stat-card">
            <i class="fas fa-eye"></i>
            <h3>0</h3>
            <p>Profile Views</p>
        </div>
        
        <div class="stat-card">
            <i class="fas fa-palette"></i>
            <h3>Active</h3>
            <p>Portfolio Status</p>
        </div>
    </div>
    
    <div class="action-buttons">
        <a href="../portfolio/add-item.php?type=project" class="btn-action primary">
            <i class="fas fa-plus"></i> Add Project
        </a>
        <a href="../portfolio/add-item.php?type=achievement" class="btn-action">
            <i class="fas fa-trophy"></i> Add Achievement
        </a>
        <a href="customize-pdf.php" class="btn-action">
            <i class="fas fa-palette"></i> Customize PDF
        </a>
        <a href="export-portfolio.php" class="btn-action">
            <i class="fas fa-download"></i> Export PDF
        </a>
    </div>
    
    <div class="dashboard-content">
        <div class="content-section">
            <div class="section-header">
                <h2>Your Portfolio Items</h2>
                <a href="../portfolio/add-item.php" class="btn-add">
                    <i class="fas fa-plus"></i> Add New
                </a>
            </div>
            
            <?php if (empty($portfolioItems)): ?>
                <div class="empty-state">
                    <i class="fas fa-folder-open"></i>
                    <h3>No portfolio items yet</h3>
                    <p>Start building your portfolio by adding your first project or achievement!</p>
                    <a href="../portfolio/add-item.php" class="btn-primary">Add Your First Item</a>
                </div>
            <?php else: ?>
                <div class="portfolio-items-list">
                    <?php foreach ($portfolioItems as $item): ?>
                        <div class="portfolio-item-card">
                            <div class="item-icon">
                                <?php if (($item['item_type'] ?? '') === 'project'): ?>
                                    <i class="fas fa-project-diagram"></i>
                                <?php elseif (($item['item_type'] ?? '') === 'achievement'): ?>
                                    <i class="fas fa-trophy"></i>
                                <?php else: ?>
                                    <i class="fas fa-star"></i>
                                <?php endif; ?>
                            </div>
                            
                            <div class="item-content">
                                <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                                <p><?php echo htmlspecialchars($item['description']); ?></p>
                                
                                <?php if (!empty($item['tags'])): ?>
                                    <div class="item-tags">
                                        <?php foreach ($item['tags'] as $tag): ?>
                                            <span class="tag"><?php echo htmlspecialchars($tag); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($item['date'])): ?>
                                    <p class="item-date">
                                        <i class="fas fa-calendar"></i> <?php echo date('M Y', strtotime($item['date'])); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="item-actions">
                                <button class="btn-icon" onclick="window.location.href='../portfolio/edit-item.php?id=<?php echo $item['id']; ?>'" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-icon" onclick="if(confirm('Are you sure you want to delete this item?')) window.location.href='../portfolio/delete-item.php?id=<?php echo $item['id']; ?>'" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="sidebar">
            <div class="sidebar-card">
                <h3>Quick Links</h3>
                <ul class="quick-links">
                    <li><a href="../portfolio/view.php?username=<?php echo $currentUser->username; ?>">
                        <i class="fas fa-eye"></i> View Public Portfolio
                    </a></li>
                    <li><a href="profile.php">
                        <i class="fas fa-user"></i> Edit Profile
                    </a></li>
                    <li><a href="settings.php">
                        <i class="fas fa-cog"></i> Settings
                    </a></li>
                    <li><a href="../../index.php">
                        <i class="fas fa-home"></i> Back to Home
                    </a></li>
                </ul>
            </div>
            
            <div class="sidebar-card">
                <h3>Profile Info</h3>
                <div class="profile-info">
                    <p><strong>Username:</strong> @<?php echo htmlspecialchars($currentUser->username); ?></p>
                    <p><strong>Program:</strong> <?php echo htmlspecialchars($currentUser->program); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($currentUser->email); ?></p>
                </div>
            </div>
            
            <div class="sidebar-card tips">
                <h3><i class="fas fa-lightbulb"></i> Tips</h3>
                <ul>
                    <li>Add detailed descriptions to your projects</li>
                    <li>Use tags to make your work discoverable</li>
                    <li>Keep your portfolio updated regularly</li>
                    <li>Customize your theme to stand out</li>
                </ul>
            </div>
        </div>
    </div>
</div>

</div>

<?php include "../../includes/footer.php"; ?>

</body>
</html>
