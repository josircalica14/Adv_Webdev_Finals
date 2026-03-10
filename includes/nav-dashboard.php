<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Use real authentication system - check if already loaded
if (!function_exists('currentUser')) {
    require_once __DIR__ . '/bootstrap.php';
}

$currentUser = currentUser();

// Get the base path - use absolute path from document root
$basePath = '/';
?>

<style>
.navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 60px;
    background: #eaeaea;
    border-bottom: 1px solid #0f0f0f;
}

.logo {
    font-weight: 900;
    font-size: 22px;
    background: #d6a5ad;
    color: #0f0f0f;
    padding: 8px 16px;
    border-radius: 8px;
    letter-spacing: 1px;
}

.nav-links {
    display: flex;
    gap: 40px;
    list-style: none;
    align-items: center;
    margin: 0;
    padding: 0;
}

.nav-links li {
    list-style: none;
}

.nav-links a {
    text-decoration: none;
    color: #0f0f0f;
    font-weight: 600;
    font-size: 15px;
    transition: color 0.2s;
    position: relative;
}

.nav-links a:hover {
    color: #d6a5ad;
}

.nav-links a::after {
    content: '';
    position: absolute;
    bottom: -4px;
    left: 0;
    width: 0;
    height: 2px;
    background: #d6a5ad;
    transition: width 0.3s;
}

.nav-links a:hover::after {
    width: 100%;
}

.nav-right {
    display: flex;
    align-items: center;
    gap: 15px;
}

.nav-profile {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 6px 16px 6px 6px;
    background: white;
    border: 1px solid #0f0f0f;
    border-radius: 25px;
    text-decoration: none;
    transition: all 0.3s;
}

.nav-profile:hover {
    background: #f5e8eb;
    border-color: #d6a5ad;
    transform: translateY(-2px);
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
}

.nav-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #d6a5ad;
}

.nav-avatar-placeholder {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #f5e8eb;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d6a5ad;
    font-size: 14px;
}

.nav-username {
    font-weight: 600;
    color: #0f0f0f;
    font-size: 14px;
}

.account-btn {
    background: #0f0f0f;
    color: white;
    padding: 10px 20px;
    border-radius: 20px;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s;
    border: 2px solid #0f0f0f;
}

.account-btn:hover {
    background: #d6a5ad;
    color: #0f0f0f;
    border-color: #0f0f0f;
}

.logout-btn {
    padding: 10px 16px;
    background: transparent;
    color: #0f0f0f;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    border: 1px solid #0f0f0f;
    border-radius: 50%;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
}

.logout-btn:hover {
    background: #c0392b;
    color: white;
    border-color: #c0392b;
    transform: translateY(-2px);
}

.login-btn {
    padding: 10px 24px;
    background: transparent;
    color: #0f0f0f;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    border: 1px solid #0f0f0f;
    border-radius: 25px;
    transition: all 0.3s;
    letter-spacing: 0.5px;
}

.login-btn:hover {
    background: white;
    border-color: #0f0f0f;
    transform: translateY(-2px);
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
}

@media (max-width: 768px) {
    .navbar {
        flex-direction: column;
        padding: 16px 20px;
        gap: 16px;
    }

    .nav-links {
        flex-wrap: wrap;
        gap: 16px;
        justify-content: center;
    }

    .nav-links a {
        font-size: 13px;
    }

    .nav-right {
        flex-wrap: wrap;
        justify-content: center;
        gap: 12px;
    }
}
</style>

<div class="navbar">
    <div class="logo">PF</div>

    <ul class="nav-links">
        <li><a href="<?php echo $basePath; ?>index.php">HOME</a></li>
        <li><a href="<?php echo $basePath; ?>index.php#skills">SKILLS</a></li>
        <li><a href="<?php echo $basePath; ?>index.php#projects">PROJECTS</a></li>
        <li><a href="<?php echo $basePath; ?>index.php#showcase">SHOWCASE</a></li>
        <li><a href="<?php echo $basePath; ?>pages/about.php">ABOUT</a></li>
        <li><a href="<?php echo $basePath; ?>pages/contact.php">CONTACT</a></li>
    </ul>

    <div class="nav-right">
        <?php if ($currentUser): ?>
            <a href="<?php echo $basePath; ?>pages/dashboard/profile.php" class="nav-profile">
                <?php if ($currentUser->profilePhotoPath && file_exists($currentUser->profilePhotoPath)): ?>
                    <img src="<?php echo htmlspecialchars($currentUser->profilePhotoPath); ?>" alt="Profile" class="nav-avatar">
                <?php else: ?>
                    <div class="nav-avatar-placeholder">
                        <i class="fas fa-user"></i>
                    </div>
                <?php endif; ?>
                <span class="nav-username"><?php echo htmlspecialchars($currentUser->fullName); ?></span>
            </a>
            <a href="<?php echo $basePath; ?>pages/dashboard/dashboard.php" class="account-btn">DASHBOARD</a>
            <a href="<?php echo $basePath; ?>auth/logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        <?php else: ?>
            <a href="<?php echo $basePath; ?>pages/auth/login.php" class="login-btn">LOGIN</a>
            <a href="<?php echo $basePath; ?>pages/auth/register.php" class="account-btn">SIGN UP</a>
        <?php endif; ?>
    </div>
</div>
