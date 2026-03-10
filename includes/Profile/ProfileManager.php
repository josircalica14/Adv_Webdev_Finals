<?php
/**
 * Profile Manager
 * 
 * Handles user profile management operations including profile updates,
 * photo uploads, and username changes with rate limiting.
 */

require_once __DIR__ . '/../Auth/User.php';
require_once __DIR__ . '/../Auth/UserRepository.php';
require_once __DIR__ . '/../Database.php';
require_once __DIR__ . '/../Logger.php';

class ProfileManager {
    private PDO $db;
    private UserRepository $userRepository;
    private Logger $logger;
    private string $uploadPath;
    private array $allowedImageTypes = ['image/jpeg', 'image/png', 'image/webp'];
    private int $maxPhotoSize = 5 * 1024 * 1024; // 5MB

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->userRepository = new UserRepository();
        $this->logger = Logger::getInstance();
        
        // Set upload path from config or use default
        $config = require __DIR__ . '/../../config/app.config.php';
        $this->uploadPath = $config['upload_path'] ?? __DIR__ . '/../../uploads/profile_photos';
        
        // Create upload directory if it doesn't exist
        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }
    }

    /**
     * Get user profile
     * 
     * @param int $userId User ID
     * @return User|null User profile or null if not found
     */
    public function getProfile(int $userId): ?User {
        try {
            $user = $this->userRepository->findById($userId);
            
            if ($user) {
                $this->logger->info("Profile retrieved", ['user_id' => $userId]);
            } else {
                $this->logger->warning("Profile not found", ['user_id' => $userId]);
            }
            
            return $user;
        } catch (Exception $e) {
            $this->logger->error("Failed to get profile", [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Update user profile
     * 
     * @param int $userId User ID
     * @param array $profileData Profile data to update
     * @return array Result with success status and errors
     */
    public function updateProfile(int $userId, array $profileData): array {
        $errors = [];
        
        try {
            // Get current user
            $user = $this->userRepository->findById($userId);
            if (!$user) {
                return [
                    'success' => false,
                    'errors' => ['User not found']
                ];
            }
            
            // Validate full name
            if (isset($profileData['full_name'])) {
                $fullName = trim($profileData['full_name']);
                if (empty($fullName)) {
                    $errors[] = 'Full name cannot be empty';
                } else {
                    $user->fullName = $fullName;
                }
            }
            
            // Validate bio
            if (isset($profileData['bio'])) {
                $user->bio = trim($profileData['bio']);
            }
            
            // Validate program
            if (isset($profileData['program'])) {
                $program = $profileData['program'];
                if (!in_array($program, ['BSIT', 'CSE'])) {
                    $errors[] = 'Invalid program. Must be BSIT or CSE';
                } else {
                    $user->program = $program;
                }
            }
            
            // Validate contact info
            if (isset($profileData['contact_info'])) {
                if (is_array($profileData['contact_info'])) {
                    $user->contactInfo = $profileData['contact_info'];
                } else {
                    $errors[] = 'Contact info must be an array';
                }
            }
            
            // Return early if validation errors
            if (!empty($errors)) {
                return [
                    'success' => false,
                    'errors' => $errors
                ];
            }
            
            // Update user
            $success = $this->userRepository->update($user);
            
            if ($success) {
                $this->logger->info("Profile updated successfully", ['user_id' => $userId]);
                return [
                    'success' => true,
                    'errors' => []
                ];
            } else {
                return [
                    'success' => false,
                    'errors' => ['Failed to update profile']
                ];
            }
            
        } catch (Exception $e) {
            $this->logger->error("Failed to update profile", [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'errors' => ['An error occurred while updating profile']
            ];
        }
    }

    /**
     * Upload profile photo
     * 
     * @param int $userId User ID
     * @param array $file Uploaded file from $_FILES
     * @return array Result with success status and errors
     */
    public function uploadProfilePhoto(int $userId, array $file): array {
        try {
            // Get current user
            $user = $this->userRepository->findById($userId);
            if (!$user) {
                return [
                    'success' => false,
                    'errors' => ['User not found']
                ];
            }
            
            // Validate file upload
            $validation = $this->validatePhotoUpload($file);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'errors' => $validation['errors']
                ];
            }
            
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'profile_' . $userId . '_' . time() . '.' . $extension;
            $filepath = $this->uploadPath . '/' . $filename;
            
            // Delete old profile photo if exists
            if ($user->profilePhotoPath && file_exists($user->profilePhotoPath)) {
                unlink($user->profilePhotoPath);
            }
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                // Update user profile photo path
                $user->profilePhotoPath = $filepath;
                $success = $this->userRepository->update($user);
                
                if ($success) {
                    $this->logger->info("Profile photo uploaded", [
                        'user_id' => $userId,
                        'filename' => $filename
                    ]);
                    return [
                        'success' => true,
                        'errors' => [],
                        'filepath' => $filepath
                    ];
                } else {
                    // Clean up uploaded file if database update fails
                    unlink($filepath);
                    return [
                        'success' => false,
                        'errors' => ['Failed to update profile photo in database']
                    ];
                }
            } else {
                return [
                    'success' => false,
                    'errors' => ['Failed to save uploaded file']
                ];
            }
            
        } catch (Exception $e) {
            $this->logger->error("Failed to upload profile photo", [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'errors' => ['An error occurred while uploading photo']
            ];
        }
    }

    /**
     * Validate photo upload
     * 
     * @param array $file Uploaded file from $_FILES
     * @return array Validation result
     */
    private function validatePhotoUpload(array $file): array {
        $errors = [];
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            switch ($file['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $errors[] = 'File size exceeds maximum allowed size';
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $errors[] = 'No file was uploaded';
                    break;
                default:
                    $errors[] = 'File upload error occurred';
            }
            return ['valid' => false, 'errors' => $errors];
        }
        
        // Check file size (5MB max)
        if ($file['size'] > $this->maxPhotoSize) {
            $errors[] = 'File size exceeds 5MB limit';
        }
        
        // Check file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $this->allowedImageTypes)) {
            $errors[] = 'Invalid file type. Only JPEG, PNG, and WebP are allowed';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Update username
     * 
     * @param int $userId User ID
     * @param string $newUsername New username
     * @return array Result with success status and errors
     */
    public function updateUsername(int $userId, string $newUsername): array {
        try {
            // Get current user
            $user = $this->userRepository->findById($userId);
            if (!$user) {
                return [
                    'success' => false,
                    'errors' => ['User not found']
                ];
            }
            
            // Check if user can change username (rate limiting)
            if (!$this->canChangeUsername($userId)) {
                return [
                    'success' => false,
                    'errors' => ['Username can only be changed once every 30 days']
                ];
            }
            
            // Validate username format
            $validation = $this->validateUsername($newUsername);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'errors' => $validation['errors']
                ];
            }
            
            // Check username uniqueness
            if ($this->userRepository->usernameExists($newUsername)) {
                return [
                    'success' => false,
                    'errors' => ['Username is already taken']
                ];
            }
            
            // Update username and timestamp
            $user->username = $newUsername;
            $user->lastUsernameChange = date('Y-m-d H:i:s');
            
            $success = $this->userRepository->update($user);
            
            if ($success) {
                $this->logger->info("Username updated", [
                    'user_id' => $userId,
                    'new_username' => $newUsername
                ]);
                return [
                    'success' => true,
                    'errors' => []
                ];
            } else {
                return [
                    'success' => false,
                    'errors' => ['Failed to update username']
                ];
            }
            
        } catch (Exception $e) {
            $this->logger->error("Failed to update username", [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'errors' => ['An error occurred while updating username']
            ];
        }
    }

    /**
     * Check if user can change username (30-day rate limit)
     * 
     * @param int $userId User ID
     * @return bool True if user can change username
     */
    public function canChangeUsername(int $userId): bool {
        try {
            $user = $this->userRepository->findById($userId);
            if (!$user) {
                return false;
            }
            
            // If never changed, allow change
            if (!$user->lastUsernameChange) {
                return true;
            }
            
            // Check if 30 days have passed
            $lastChange = new DateTime($user->lastUsernameChange);
            $now = new DateTime();
            $daysSinceChange = $now->diff($lastChange)->days;
            
            return $daysSinceChange >= 30;
            
        } catch (Exception $e) {
            $this->logger->error("Failed to check username change eligibility", [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Validate username format
     * 
     * @param string $username Username to validate
     * @return array Validation result
     */
    private function validateUsername(string $username): array {
        $errors = [];
        
        // Check length
        if (strlen($username) < 3) {
            $errors[] = 'Username must be at least 3 characters long';
        }
        
        if (strlen($username) > 50) {
            $errors[] = 'Username must not exceed 50 characters';
        }
        
        // Check format (alphanumeric, hyphens, underscores only)
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $username)) {
            $errors[] = 'Username can only contain letters, numbers, hyphens, and underscores';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}
