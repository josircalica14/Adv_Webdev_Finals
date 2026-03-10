<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/Profile/ProfileManager.php';
require_once __DIR__ . '/../../includes/Auth/CSRFProtection.php';

// Require authentication
$user = requireAuth();

$error = '';
$success = '';
$csrf = new CSRFProtection();
$profileManager = new ProfileManager();

// Get current profile
$profile = $profileManager->getProfile($user->id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!$csrf->validateRequest()) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $fullName = $_POST['full_name'] ?? '';
        $bio = $_POST['bio'] ?? '';
        $program = $_POST['program'] ?? '';
        $contactInfo = [
            'phone' => $_POST['phone'] ?? '',
            'linkedin' => $_POST['linkedin'] ?? '',
            'github' => $_POST['github'] ?? '',
            'website' => $_POST['website'] ?? ''
        ];
        
        $profileData = [
            'full_name' => $fullName,
            'bio' => $bio,
            'program' => $program,
            'contact_info' => $contactInfo
        ];
        
        $result = $profileManager->updateProfile($user->id, $profileData);
        
        if ($result) {
            $success = 'Profile updated successfully!';
            // Refresh profile data
            $profile = $profileManager->getProfile($user->id);
            // Update session
            $_SESSION['user_name'] = $fullName;
            $_SESSION['user_program'] = $program;
        } else {
            $error = 'Failed to update profile. Please try again.';
        }
    }
}

// Handle profile photo upload
if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
    if (!$csrf->validateRequest()) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $result = $profileManager->uploadProfilePhoto($user->id, $_FILES['profile_photo']);
        
        if ($result['success']) {
            $success = 'Profile photo updated successfully!';
            $profile = $profileManager->getProfile($user->id);
        } else {
            $error = $result['error'] ?? 'Failed to upload photo. Please try again.';
        }
    }
}

// Get contact info - User object has contactInfo property
$contactInfoRaw = $profile ? $profile->contactInfo : null;
// Check if it's already an array or needs to be decoded
if (is_array($contactInfoRaw)) {
    $contactInfo = $contactInfoRaw;
} elseif (is_string($contactInfoRaw)) {
    $contactInfo = json_decode($contactInfoRaw, true) ?? [];
} else {
    $contactInfo = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Portfolio Showcase</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/dashboard.css">
    <link rel="stylesheet" href="../../css/profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php 
    include '../../includes/header.php'; 
    include '../../includes/nav-dashboard.php';
    ?>
    
    <main class="dashboard-main">
        <div class="dashboard-container">
            <div class="dashboard-header">
                <h1><i class="fas fa-user-circle"></i> My Profile</h1>
                <p>Manage your personal information and settings</p>
                <a href="dashboard.php" class="back-to-dashboard">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <div class="profile-grid">
                <!-- Profile Photo Section -->
                <div class="profile-photo-section card">
                    <h2><i class="fas fa-camera"></i> Profile Photo</h2>
                    
                    <div class="profile-photo-container">
                        <?php if (!empty($profile->profilePhotoPath)): ?>
                            <img src="<?php echo htmlspecialchars($profile->profilePhotoPath); ?>" 
                                 alt="Profile Photo" 
                                 class="profile-photo-preview">
                        <?php else: ?>
                            <div class="profile-photo-placeholder">
                                <i class="fas fa-user"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <form method="POST" enctype="multipart/form-data" class="profile-photo-form">
                        <?php echo $csrf->getTokenField(); ?>
                        
                        <div class="form-group">
                            <label for="profile_photo" class="file-upload-label">
                                <i class="fas fa-upload"></i> Choose Photo
                            </label>
                            <input 
                                type="file" 
                                id="profile_photo" 
                                name="profile_photo" 
                                accept="image/jpeg,image/png,image/webp"
                                onchange="this.form.submit()"
                            >
                            <small class="form-hint">
                                JPEG, PNG, or WebP. Max 5MB.
                            </small>
                        </div>
                    </form>
                </div>
                
                <!-- Profile Information Section -->
                <div class="profile-info-section card">
                    <h2><i class="fas fa-info-circle"></i> Profile Information</h2>
                    
                    <form method="POST" class="profile-form">
                        <?php echo $csrf->getTokenField(); ?>
                        
                        <div class="form-group">
                            <label for="full_name">
                                <i class="fas fa-user"></i> Full Name
                            </label>
                            <input 
                                type="text" 
                                id="full_name" 
                                name="full_name" 
                                required
                                value="<?php echo htmlspecialchars($profile->fullName); ?>"
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="email">
                                <i class="fas fa-envelope"></i> Email Address
                            </label>
                            <input 
                                type="email" 
                                id="email" 
                                value="<?php echo htmlspecialchars($profile->email); ?>"
                                disabled
                            >
                            <small class="form-hint">Email cannot be changed</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="username">
                                <i class="fas fa-at"></i> Username
                            </label>
                            <input 
                                type="text" 
                                id="username" 
                                value="<?php echo htmlspecialchars($profile->username); ?>"
                                disabled
                            >
                            <small class="form-hint">
                                <a href="change-username.php">Change username</a>
                            </small>
                        </div>
                        
                        <div class="form-group">
                            <label for="program">
                                <i class="fas fa-graduation-cap"></i> Program
                            </label>
                            <select id="program" name="program" required>
                                <option value="BSIT" <?php echo $profile->program === 'BSIT' ? 'selected' : ''; ?>>
                                    BSIT - Information Technology
                                </option>
                                <option value="CSE" <?php echo $profile->program === 'CSE' ? 'selected' : ''; ?>>
                                    CSE - Computer Science & Engineering
                                </option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="bio">
                                <i class="fas fa-align-left"></i> Bio
                            </label>
                            <textarea 
                                id="bio" 
                                name="bio" 
                                rows="4"
                                placeholder="Tell us about yourself..."
                            ><?php echo htmlspecialchars($profile->bio ?? ''); ?></textarea>
                        </div>
                        
                        <h3><i class="fas fa-link"></i> Contact Information</h3>
                        
                        <div class="form-group">
                            <label for="phone">
                                <i class="fas fa-phone"></i> Phone
                            </label>
                            <input 
                                type="tel" 
                                id="phone" 
                                name="phone" 
                                placeholder="+1 (555) 123-4567"
                                value="<?php echo htmlspecialchars($contactInfo['phone'] ?? ''); ?>"
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="linkedin">
                                <i class="fab fa-linkedin"></i> LinkedIn
                            </label>
                            <input 
                                type="url" 
                                id="linkedin" 
                                name="linkedin" 
                                placeholder="https://linkedin.com/in/yourprofile"
                                value="<?php echo htmlspecialchars($contactInfo['linkedin'] ?? ''); ?>"
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="github">
                                <i class="fab fa-github"></i> GitHub
                            </label>
                            <input 
                                type="url" 
                                id="github" 
                                name="github" 
                                placeholder="https://github.com/yourusername"
                                value="<?php echo htmlspecialchars($contactInfo['github'] ?? ''); ?>"
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="website">
                                <i class="fas fa-globe"></i> Website
                            </label>
                            <input 
                                type="url" 
                                id="website" 
                                name="website" 
                                placeholder="https://yourwebsite.com"
                                value="<?php echo htmlspecialchars($contactInfo['website'] ?? ''); ?>"
                            >
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn-primary">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                            <a href="change-password.php" class="btn-secondary">
                                <i class="fas fa-key"></i> Change Password
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
    
    <?php include '../../includes/footer.php'; ?>
</body>
</html>
