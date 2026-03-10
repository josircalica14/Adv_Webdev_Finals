<?php
// Redirect to main index page
header('Location: index.php');
exit;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portfolio Showcase - BSIT & CSE</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/showcase.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="showcase-nav">
        <div class="nav-container">
            <div class="nav-brand">
                <h1>Portfolio Showcase</h1>
                <p>BSIT & CSE Students</p>
            </div>
            
            <div class="nav-links">
                <?php if ($currentUser): ?>
                    <a href="dashboard.php" class="nav-link">
                        <i class="fas fa-th-large"></i> Dashboard
                    </a>
                    <a href="auth/logout.php" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                    <span class="nav-user">
                        <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($currentUser['full_name']); ?>
                    </span>
                <?php else: ?>
                    <a href="login.php" class="nav-link">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                    <a href="register.php" class="nav-link btn-primary">
                        <i class="fas fa-user-plus"></i> Sign Up
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <h1 class="hero-title">Discover Talented Students</h1>
            <p class="hero-subtitle">Browse portfolios from BSIT and CSE students showcasing their projects, achievements, and skills</p>
            <?php if (!$currentUser): ?>
                <a href="register.php" class="btn-hero">Create Your Portfolio</a>
            <?php endif; ?>
        </div>
    </section>

    <!-- Search and Filter -->
    <section class="filter-section">
        <div class="container">
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Search by name, skills, or projects...">
            </div>
            
            <div class="filter-controls">
                <button class="filter-btn active" data-program="all">
                    <i class="fas fa-users"></i> All Students
                </button>
                <button class="filter-btn" data-program="BSIT">
                    <i class="fas fa-laptop-code"></i> BSIT
                </button>
                <button class="filter-btn" data-program="CSE">
                    <i class="fas fa-microchip"></i> CSE
                </button>
            </div>
            
            <div class="sort-controls">
                <label>Sort by:</label>
                <select id="sortSelect">
                    <option value="recent">Most Recent</option>
                    <option value="name">Name (A-Z)</option>
                </select>
            </div>
        </div>
    </section>

    <!-- Portfolio Grid -->
    <section class="portfolio-grid-section">
        <div class="container">
            <div class="results-info">
                <span id="resultsCount"><?php echo count($portfolios); ?></span> portfolios found
            </div>
            
            <div class="portfolio-grid" id="portfolioGrid">
                <?php foreach ($portfolios as $user): ?>
                    <div class="portfolio-card" data-program="<?php echo $user['program']; ?>">
                        <div class="portfolio-card-header">
                            <div class="profile-photo">
                                <?php if (isset($user['profile_photo']) && file_exists($user['profile_photo'])): ?>
                                    <img src="<?php echo $user['profile_photo']; ?>" alt="<?php echo htmlspecialchars($user['full_name']); ?>">
                                <?php else: ?>
                                    <div class="profile-placeholder">
                                        <i class="fas fa-user"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <span class="program-badge <?php echo strtolower($user['program']); ?>">
                                <?php echo $user['program']; ?>
                            </span>
                        </div>
                        
                        <div class="portfolio-card-body">
                            <h3 class="portfolio-name"><?php echo htmlspecialchars($user['full_name']); ?></h3>
                            <p class="portfolio-username">@<?php echo htmlspecialchars($user['username']); ?></p>
                            <p class="portfolio-bio"><?php echo htmlspecialchars($user['bio']); ?></p>
                            
                            <?php
                            $items = getPortfolioItems($user['id']);
                            $projectCount = count(array_filter($items, fn($item) => $item['type'] === 'project'));
                            $achievementCount = count(array_filter($items, fn($item) => $item['type'] === 'achievement'));
                            ?>
                            
                            <div class="portfolio-stats">
                                <span><i class="fas fa-project-diagram"></i> <?php echo $projectCount; ?> Projects</span>
                                <span><i class="fas fa-trophy"></i> <?php echo $achievementCount; ?> Achievements</span>
                            </div>
                        </div>
                        
                        <div class="portfolio-card-footer">
                            <a href="portfolio-view.php?username=<?php echo $user['username']; ?>" class="btn-view-portfolio">
                                View Portfolio <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <div class="pagination">
                <button class="page-btn" disabled><i class="fas fa-chevron-left"></i> Previous</button>
                <span class="page-info">Page 1 of 1</span>
                <button class="page-btn" disabled>Next <i class="fas fa-chevron-right"></i></button>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="showcase-footer">
        <div class="container">
            <p>&copy; 2024 Student Portfolio Showcase. All rights reserved.</p>
            <p>Empowering BSIT and CSE students to showcase their talent</p>
        </div>
    </footer>

    <script src="js/showcase.js"></script>
</body>
</html>
